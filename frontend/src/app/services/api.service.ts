import { Injectable, inject, signal, computed } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, catchError, map, of, tap } from 'rxjs';

export interface Category {
  id: number;
  name: string;
  slug: string;
  description?: string;
  products_count?: number;
}

export interface Product {
  id: number;
  category_id: number;
  category?: {
    id: number;
    name: string;
    slug: string;
  };
  name: string;
  slug: string;
  description: string;
  price: number;
  stock: number;
  specs?: Record<string, string>;
  image_url?: string;
}

export interface CartItem {
  product: Product;
  quantity: number;
}

export interface User {
  id: number;
  name: string;
  email: string;
  address?: string;
  phone?: string;
  role_id?: number;
  role?: {
    id: number;
    name: string;
    slug: string;
  };
}

export interface ChatMessage {
  id?: number;
  role: 'user' | 'assistant';
  content: string;
  timestamp?: Date;
}

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  private http = inject(HttpClient);

  readonly USERS_URL = 'http://localhost:8001/api';
  readonly PRODUCTS_URL = 'http://localhost:8002/api';
  readonly PAYMENTS_URL = 'http://localhost:8003/api';
  readonly ASSISTANT_URL = 'http://localhost:8004/api';

  // State Signals
  currentUser = signal<User | null>(this.getStoredUser());
  token = signal<string | null>(localStorage.getItem('techhouse_token'));
  cartItems = signal<CartItem[]>(this.getStoredCart());
  conversationId = signal<number | null>(null);

  // Computed Cart metrics
  cartCount = computed(() => this.cartItems().reduce((acc, item) => acc + item.quantity, 0));
  cartTotal = computed(() => this.cartItems().reduce((acc, item) => acc + (item.product.price * item.quantity), 0));

  private getStoredUser(): User | null {
    const raw = localStorage.getItem('techhouse_user');
    return raw ? JSON.parse(raw) : null;
  }

  private getStoredCart(): CartItem[] {
    const raw = localStorage.getItem('techhouse_cart');
    return raw ? JSON.parse(raw) : [];
  }

  private saveCart(items: CartItem[]) {
    this.cartItems.set(items);
    localStorage.setItem('techhouse_cart', JSON.stringify(items));
  }

  // -------------------------------------------------------------
  // PRODUCTS & CATEGORIES
  // -------------------------------------------------------------
  getCategories(): Observable<Category[]> {
    return this.http.get<{ data: Category[] }>(`${this.PRODUCTS_URL}/categories`).pipe(
      map(res => res.data),
      catchError(() => of([]))
    );
  }

  getProducts(category?: string, search?: string): Observable<Product[]> {
    let url = `${this.PRODUCTS_URL}/products?per_page=30`;
    if (category && category !== 'all') {
      url += `&category=${encodeURIComponent(category)}`;
    }
    if (search && search.trim()) {
      url += `&search=${encodeURIComponent(search.trim())}`;
    }

    return this.http.get<{ data: Product[] }>(url).pipe(
      map(res => res.data),
      catchError(() => of([]))
    );
  }

  // -------------------------------------------------------------
  // CART OPERATIONS
  // -------------------------------------------------------------
  addToCart(product: Product, quantity = 1) {
    const current = [...this.cartItems()];
    const index = current.findIndex(i => i.product.id === product.id);

    if (index > -1) {
      current[index] = {
        ...current[index],
        quantity: Math.min(current[index].quantity + quantity, product.stock)
      };
    } else {
      current.push({ product, quantity: Math.min(quantity, product.stock) });
    }

    this.saveCart(current);
  }

  updateQuantity(productId: number, quantity: number) {
    let current = [...this.cartItems()];
    if (quantity <= 0) {
      current = current.filter(i => i.product.id !== productId);
    } else {
      current = current.map(item => {
        if (item.product.id === productId) {
          return { ...item, quantity: Math.min(quantity, item.product.stock) };
        }
        return item;
      });
    }
    this.saveCart(current);
  }

  removeFromCart(productId: number) {
    const filtered = this.cartItems().filter(i => i.product.id !== productId);
    this.saveCart(filtered);
  }

  clearCart() {
    this.saveCart([]);
  }

  // -------------------------------------------------------------
  // AUTHENTICATION
  // -------------------------------------------------------------
  login(email: string, password: string): Observable<any> {
    return this.http.post<any>(`${this.USERS_URL}/auth/login`, { email, password }).pipe(
      tap(res => {
        if (res.data?.token) {
          localStorage.setItem('techhouse_token', res.data.token);
          localStorage.setItem('techhouse_user', JSON.stringify(res.data.user));
          this.token.set(res.data.token);
          this.currentUser.set(res.data.user);
        }
      })
    );
  }

  register(data: { name: string; email: string; password: string; password_confirmation: string; address?: string; phone?: string }): Observable<any> {
    return this.http.post<any>(`${this.USERS_URL}/auth/register`, data).pipe(
      tap(res => {
        if (res.data?.token) {
          localStorage.setItem('techhouse_token', res.data.token);
          localStorage.setItem('techhouse_user', JSON.stringify(res.data.user));
          this.token.set(res.data.token);
          this.currentUser.set(res.data.user);
        }
      })
    );
  }

  logout() {
    const t = this.token();
    if (t) {
      const headers = new HttpHeaders({ Authorization: `Bearer ${t}` });
      this.http.post(`${this.USERS_URL}/auth/logout`, {}, { headers }).subscribe();
    }
    localStorage.removeItem('techhouse_token');
    localStorage.removeItem('techhouse_user');
    this.token.set(null);
    this.currentUser.set(null);
  }

  // -------------------------------------------------------------
  // PAYMENTS & CHECKOUT
  // -------------------------------------------------------------
  checkout(paymentMethod: string): Observable<any> {
    const items = this.cartItems().map(item => ({
      product_id: item.product.id,
      quantity: item.quantity,
      unit_price: item.product.price
    }));

    const userId = this.currentUser()?.id || 1;

    return this.http.post<any>(`${this.PAYMENTS_URL}/payments/checkout`, {
      user_id: userId,
      payment_method: paymentMethod,
      items
    }).pipe(
      tap(() => this.clearCart())
    );
  }

  // -------------------------------------------------------------
  // ASSISTANT CHAT
  // -------------------------------------------------------------
  sendChatMessage(message: string): Observable<any> {
    const body: any = {
      message,
      user_id: this.currentUser()?.id || null
    };

    if (this.conversationId()) {
      body.conversation_id = this.conversationId();
    }

    return this.http.post<any>(`${this.ASSISTANT_URL}/assistant/chat`, body).pipe(
      tap(res => {
        if (res.conversation_id) {
          this.conversationId.set(res.conversation_id);
        }
      })
    );
  }
}
