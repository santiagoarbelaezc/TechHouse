<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Laptops & Workstations',
                'slug' => 'laptops',
                'description' => 'Portátiles ultraligeros, estaciones de trabajo y laptops gamer de alto rendimiento.',
            ],
            [
                'name' => 'Tarjetas Gráficas',
                'slug' => 'graficas',
                'description' => 'GPUs de última arquitectura para renderizado, gaming 4K e inteligencia artificial.',
            ],
            [
                'name' => 'Procesadores',
                'slug' => 'procesadores',
                'description' => 'CPUs multicore para multitarea extrema, compilación y rendimiento sin compromisos.',
            ],
            [
                'name' => 'Monitores Gamer & 4K',
                'slug' => 'monitores',
                'description' => 'Paneles QD-OLED y Mini-LED de alta tasa de refresco y calibración de color profesional.',
            ],
            [
                'name' => 'Periféricos & Setup',
                'slug' => 'perifericos',
                'description' => 'Teclados mecánicos custom, ratones de precisión y audio de alta fidelidad.',
            ],
        ];

        $createdCategories = [];
        foreach ($categories as $cat) {
            $createdCategories[$cat['slug']] = Category::create($cat);
        }

        $products = [
            [
                'category_id' => $createdCategories['laptops']->id,
                'name' => 'MacBook Pro 16" M3 Max',
                'slug' => 'macbook-pro-16-m3-max',
                'description' => 'La laptop profesional definitiva para desarrolladores y creadores. Impulsada por el chip Apple M3 Max.',
                'price' => 3499.00,
                'stock' => 12,
                'specs' => [
                    'Procesador' => 'Apple M3 Max (16 CPU / 40 GPU)',
                    'Memoria RAM' => '36 GB Unificada',
                    'Almacenamiento' => '1 TB SSD PCIe NVMe',
                    'Pantalla' => '16.2" Liquid Retina XDR 120Hz ProMotion',
                    'Batería' => 'Hasta 22 horas de autonomía',
                ],
                'image_url' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'category_id' => $createdCategories['laptops']->id,
                'name' => 'ASUS ROG Zephyrus G16 OLED',
                'slug' => 'asus-rog-zephyrus-g16-oled',
                'description' => 'Chasis ultradelgado de aluminio CNC, gráficos RTX 4080 y la primera pantalla ROG Nebula OLED de 240Hz.',
                'price' => 2499.00,
                'stock' => 8,
                'specs' => [
                    'Procesador' => 'Intel Core Ultra 9 185H',
                    'GPU' => 'NVIDIA GeForce RTX 4080 12GB',
                    'Memoria RAM' => '32 GB LPDDR5X 7467MHz',
                    'Pantalla' => '16" 2.5K OLED 240Hz 0.2ms HDR True Black',
                ],
                'image_url' => 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'category_id' => $createdCategories['graficas']->id,
                'name' => 'NVIDIA GeForce RTX 4090 24GB',
                'slug' => 'nvidia-geforce-rtx-4090-24gb',
                'description' => 'La tarjeta gráfica para gaming y aceleración de IA más potente del mundo con arquitectura Ada Lovelace.',
                'price' => 1899.00,
                'stock' => 6,
                'specs' => [
                    'VRAM' => '24 GB GDDR6X 384-bit',
                    'Núcleos CUDA' => '16,384',
                    'Tecnología' => 'DLSS 3.5 con Frame Generation',
                    'Puertos' => '3x DisplayPort 1.4a, 1x HDMI 2.1a',
                ],
                'image_url' => 'https://images.unsplash.com/photo-1587202372775-e229f172b9d7?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'category_id' => $createdCategories['graficas']->id,
                'name' => 'ASUS ROG Strix RTX 4070 Ti Super',
                'slug' => 'asus-rog-strix-rtx-4070-ti-super',
                'description' => 'Rendimiento térmico superior con ventiladores Axial-tech, backplate ventilado y disipador masivo de 3.15 slots.',
                'price' => 899.00,
                'stock' => 15,
                'specs' => [
                    'VRAM' => '16 GB GDDR6X 256-bit',
                    'Núcleos CUDA' => '8,448',
                    'Iluminación' => 'Aura Sync ARGB programable',
                ],
                'image_url' => 'https://images.unsplash.com/photo-1591488320449-011701bb6704?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'category_id' => $createdCategories['procesadores']->id,
                'name' => 'AMD Ryzen 9 7950X3D',
                'slug' => 'amd-ryzen-9-7950x3d',
                'description' => 'El procesador definitivo para gaming y creación de contenido con tecnología de caché 3D vertical.',
                'price' => 599.00,
                'stock' => 18,
                'specs' => [
                    'Núcleos / Hilos' => '16 Núcleos / 32 Hilos',
                    'Frecuencia' => 'Hasta 5.7 GHz Max Boost',
                    'Caché L3' => '144 MB 3D V-Cache',
                    'Socket' => 'AMD AM5 (DDR5 & PCIe 5.0)',
                ],
                'image_url' => 'https://images.unsplash.com/photo-1555680202-c86f0e12f086?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'category_id' => $createdCategories['monitores']->id,
                'name' => 'Samsung Odyssey Neo G9 49" Curvo',
                'slug' => 'samsung-odyssey-neo-g9-49-curvo',
                'description' => 'Inmersión visual colosal en formato 32:9 equivalente a dos pantallas 1440p con curvatura 1000R.',
                'price' => 1399.00,
                'stock' => 5,
                'specs' => [
                    'Resolución' => 'Dual QHD 5120 x 1440',
                    'Tecnología Panel' => 'Quantum Mini-LED Quantum HDR 2000',
                    'Tasa de Refresco' => '240Hz / 1ms tiempo de respuesta',
                    'Curvatura' => '1000R ultraconfortable',
                ],
                'image_url' => 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'category_id' => $createdCategories['monitores']->id,
                'name' => 'Alienware 34" Curved QD-OLED',
                'slug' => 'alienware-34-curved-qd-oled',
                'description' => 'Colores cinematográficos DCI-P3 99.3%, negros absolutos infinitos y tasa ultrarrápida de 175Hz.',
                'price' => 849.00,
                'stock' => 9,
                'specs' => [
                    'Resolución' => 'WQHD 3440 x 1440 UltraWide',
                    'Panel' => 'Quantum Dot OLED',
                    'Tiempo Respuesta' => '0.1 ms GtG',
                    'Garantía' => '3 años con cobertura anti-burn in',
                ],
                'image_url' => 'https://images.unsplash.com/photo-1585792180666-f7347c490ee2?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'category_id' => $createdCategories['perifericos']->id,
                'name' => 'Keychron Q1 Pro Wireless Custom',
                'slug' => 'keychron-q1-pro-wireless-custom',
                'description' => 'Teclado mecánico custom 75% totalmente programable con QMK/VIA, chasis de aluminio macizo y conectividad Bluetooth.',
                'price' => 199.00,
                'stock' => 25,
                'specs' => [
                    'Diseño' => '75% con perilla rotatoria multimedia',
                    'Switches' => 'Gateron Jupiter Brown lubricados de fábrica',
                    'Construcción' => 'Doble junta de montaje (Double Gasket)',
                    'Compatibilidad' => 'macOS & Windows nativo',
                ],
                'image_url' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'category_id' => $createdCategories['perifericos']->id,
                'name' => 'Logitech G PRO X Superlight 2',
                'slug' => 'logitech-g-pro-x-superlight-2',
                'description' => 'El ratón inalámbrico de competición elegido por los profesionales de esports, con peso récord de 60 gramos.',
                'price' => 159.00,
                'stock' => 30,
                'specs' => [
                    'Sensor' => 'HERO 2 con hasta 32.000 DPI y 500+ IPS',
                    'Switches' => 'LIGHTFORCE Híbridos Óptico-Mecánicos',
                    'Tasa de Sondeo' => 'Hasta 4.000 Hz con tecnología LIGHTSPEED',
                    'Carga' => 'USB-C y compatible con Powerplay',
                ],
                'image_url' => 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?auto=format&fit=crop&w=800&q=80',
            ],
        ];

        foreach ($products as $prod) {
            Product::create($prod);
        }
    }
}
