import { Component, OnInit, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ApiService, Product, Category, ChatMessage } from './services/api.service';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './app.component.html',
  styleUrl: './app.component.css'
})
export class AppComponent implements OnInit {
  api = inject(ApiService);

  // UI State Signals
  categories = signal<Category[]>([]);
  products = signal<Product[]>([]);
  selectedCategory = signal<string>('all');
  searchQuery = signal<string>('');
  loading = signal<boolean>(false);

  // Modals & Panels State
  isCartOpen = signal<boolean>(false);
  isChatOpen = signal<boolean>(false);
  isAuthModalOpen = signal<boolean>(false);
  authTab = signal<'login' | 'register'>('login');
  selectedProduct = signal<Product | null>(null);

  // Checkout State
  selectedPaymentMethod = signal<string>('credit_card');
  checkoutLoading = signal<boolean>(false);
  checkoutSuccess = signal<{ orderId: number; reference: string; total: number } | null>(null);
  checkoutError = signal<string | null>(null);

  // Auth Form State
  authEmail = signal<string>('cliente@techhouse.test');
  authPassword = signal<string>('password');
  authName = signal<string>('Alejandro Gomez');
  authAddress = signal<string>('Carrera 15 #80-45');
  authPhone = signal<string>('+57 311 222 3344');
  authError = signal<string | null>(null);
  authLoading = signal<boolean>(false);

  // Chat State
  chatInput = signal<string>('');
  chatLoading = signal<boolean>(false);
  chatMessages = signal<ChatMessage[]>([
    {
      role: 'assistant',
      content: '¡Hola! Soy tu asistente inteligente de TechHouse. ¿Buscas alguna laptop, GPU o componente específico para tu setup?'
    }
  ]);

  // Hero interactive tabs
  heroTab = signal<number>(0);
  heroTabs = [
    { title: 'Laptops Gamer & Pro', cat: 'laptops' },
    { title: 'Tarjetas Gráficas RTX', cat: 'graficas' },
    { title: 'Procesadores & Placas', cat: 'procesadores' },
    { title: 'Monitores Curvos 4K', cat: 'monitores' },
    { title: 'Setup & Periféricos RGB', cat: 'perifericos' }
  ];

  ngOnInit() {
    this.loadCatalog();
  }

  loadCatalog() {
    this.loading.set(true);

    this.api.getCategories().subscribe(cats => {
      this.categories.set(cats);
    });

    this.fetchProducts();
  }

  fetchProducts() {
    this.loading.set(true);
    this.api.getProducts(this.selectedCategory(), this.searchQuery()).subscribe({
      next: (prods) => {
        this.products.set(prods);
        this.loading.set(false);
      },
      error: () => {
        this.loading.set(false);
      }
    });
  }

  selectCategory(slug: string) {
    this.selectedCategory.set(slug);
    this.fetchProducts();
  }

  onSearchChange() {
    this.fetchProducts();
  }

  setHeroTab(index: number) {
    this.heroTab.set(index);
    const cat = this.heroTabs[index].cat;
    this.selectCategory(cat);
    const section = document.getElementById('catalog-section');
    if (section) {
      section.scrollIntoView({ behavior: 'smooth' });
    }
  }

  // Cart operations
  addToCart(product: Product, event?: Event) {
    if (event) {
      event.stopPropagation();
    }
    this.api.addToCart(product);
  }

  openProductDetail(product: Product) {
    this.selectedProduct.set(product);
  }

  closeProductDetail() {
    this.selectedProduct.set(null);
  }

  // Checkout process
  processCheckout() {
    if (this.api.cartItems().length === 0) return;

    this.checkoutLoading.set(true);
    this.checkoutError.set(null);

    this.api.checkout(this.selectedPaymentMethod()).subscribe({
      next: (res) => {
        this.checkoutLoading.set(false);
        this.checkoutSuccess.set({
          orderId: res.data?.order?.id || 1,
          reference: res.data?.payment?.transaction_reference || 'TXN-CONFIRMED',
          total: res.data?.payment?.amount || this.api.cartTotal()
        });
      },
      error: (err) => {
        this.checkoutLoading.set(false);
        const msg = err.error?.message || 'Error procesando el checkout transaccional.';
        this.checkoutError.set(msg);
      }
    });
  }

  // Chatbot operations
  sendChatMessage() {
    const text = this.chatInput().trim();
    if (!text || this.chatLoading()) return;

    // Add user message immediately to the view
    this.chatMessages.update(msgs => [...msgs, { role: 'user', content: text }]);
    this.chatInput.set('');
    this.chatLoading.set(true);

    this.api.sendChatMessage(text).subscribe({
      next: (res) => {
        const reply = res.assistant_message?.content || '¡Entendido! Puedo ayudarte a encontrar el mejor componente para tus necesidades.';
        this.chatMessages.update(msgs => [...msgs, { role: 'assistant', content: reply }]);
        this.chatLoading.set(false);
      },
      error: () => {
        this.chatMessages.update(msgs => [...msgs, {
          role: 'assistant',
          content: 'No pude conectar con el servicio de IA en este momento, pero puedes explorar nuestro catálogo en la tienda.'
        }]);
        this.chatLoading.set(false);
      }
    });
  }

  // Auth operations
  submitAuth() {
    this.authError.set(null);
    this.authLoading.set(true);

    if (this.authTab() === 'login') {
      this.api.login(this.authEmail(), this.authPassword()).subscribe({
        next: () => {
          this.authLoading.set(false);
          this.isAuthModalOpen.set(false);
        },
        error: (err) => {
          this.authLoading.set(false);
          this.authError.set(err.error?.message || 'Credenciales inválidas.');
        }
      });
    } else {
      this.api.register({
        name: this.authName(),
        email: this.authEmail(),
        password: this.authPassword(),
        password_confirmation: this.authPassword(),
        address: this.authAddress(),
        phone: this.authPhone()
      }).subscribe({
        next: () => {
          this.authLoading.set(false);
          this.isAuthModalOpen.set(false);
        },
        error: (err) => {
          this.authLoading.set(false);
          this.authError.set(err.error?.message || 'Error en el registro.');
        }
      });
    }
  }

  logout() {
    this.api.logout();
  }

  // Helpers for Object.entries in templates
  getSpecsEntries(specs?: Record<string, string>): { key: string; value: string }[] {
    if (!specs) return [];
    return Object.entries(specs).map(([key, value]) => ({ key, value }));
  }

  // Maps category slug or product name → local product image asset
  getCategoryImage(slug?: string, productName?: string): string {
    if (productName) {
      const lower = productName.toLowerCase();
      if (lower.includes('mouse') || lower.includes('ratón') || lower.includes('raton')) return 'assets/products/mouse.jpg';
      if (lower.includes('headset') || lower.includes('auricular') || lower.includes('audifono') || lower.includes('audífono')) return 'assets/products/headset.jpg';
      if (lower.includes('micrófono') || lower.includes('microfono')) return 'assets/products/microphone.jpg';
      if (lower.includes('webcam') || lower.includes('cámara') || lower.includes('camara')) return 'assets/products/webcam.jpg';
      if (lower.includes('ram')) return 'assets/products/ram.jpg';
      if (lower.includes('ssd') || lower.includes('nvme')) return 'assets/products/ssd.jpg';
      if (lower.includes('motherboard') || lower.includes('placa base') || lower.includes('tarjeta madre')) return 'assets/products/motherboard.jpg';
      if (lower.includes('silla')) return 'assets/products/chair.jpg';
      if (lower.includes('smartwatch') || lower.includes('reloj')) return 'assets/products/smartwatch.jpg';
      if (lower.includes('teclado') || lower.includes('keyboard')) return 'assets/products/keyboard.jpg';
      if (lower.includes('monitor') || lower.includes('pantalla')) return 'assets/products/monitor.jpg';
      if (lower.includes('laptop') || lower.includes('macbook') || lower.includes('notebook')) return 'assets/products/laptop.jpg';
      if (lower.includes('rtx') || lower.includes('geforce') || lower.includes('gráfica') || lower.includes('grafica') || lower.includes('gpu')) return 'assets/products/gpu.jpg';
      if (lower.includes('ryzen') || lower.includes('core') || lower.includes('procesador') || lower.includes('cpu')) return 'assets/products/cpu.jpg';
    }

    const map: Record<string, string> = {
      laptops:       'assets/products/laptop.jpg',
      graficas:      'assets/products/gpu.jpg',
      procesadores:  'assets/products/cpu.jpg',
      monitores:     'assets/products/monitor.jpg',
      perifericos:   'assets/products/keyboard.jpg',
      mouse:         'assets/products/mouse.jpg',
      audio:         'assets/products/headset.jpg',
      componentes:   'assets/products/motherboard.jpg',
      almacenamiento:'assets/products/ssd.jpg',
    };
    return (slug && map[slug]) ? map[slug] : 'assets/products/workstation.jpg';
  }

  // Fallback when an image fails to load
  onImgError(event: Event, slug?: string, productName?: string): void {
    const el = event.target as HTMLImageElement;
    // Prevent infinite loop if fallback also fails
    el.onerror = null;
    el.src = this.getCategoryImage(slug, productName);
  }
}

