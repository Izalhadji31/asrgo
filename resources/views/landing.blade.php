<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASR GO - Travel Application</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="bg-white">
    <!-- Navigation -->
    <nav class="bg-white shadow-md fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <span class="text-2xl font-bold text-blue-900">ASR GO</span>
                </div>
                <div class="hidden md:flex space-x-8">
                    <a href="#home" class="text-gray-700 hover:text-blue-900 transition">Home</a>
                    <a href="#features" class="text-gray-700 hover:text-blue-900 transition">Features</a>
                    <a href="#about" class="text-gray-700 hover:text-blue-900 transition">About</a>
                    <a href="#contact" class="text-gray-700 hover:text-blue-900 transition">Contact</a>
                </div>
                <div class="flex space-x-4">
                    <a href="{{ route('login') }}" class="text-blue-900 hover:text-blue-800 transition">Login</a>
                    <a href="{{ route('register') }}" class="bg-blue-900 text-white px-4 py-2 rounded-lg hover:bg-blue-800 transition">Register</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="bg-gradient-to-br from-blue-900 to-blue-950 min-h-screen flex items-center pt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 w-full">
            <div class="text-center text-white mb-12">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6">
                    Travel Made Simple with ASR GO
                </h1>
                <p class="text-xl text-blue-100 max-w-3xl mx-auto">
                    Your trusted travel partner for comfortable and reliable journeys. Book trips, track drivers, and enjoy seamless travel experiences.
                </p>
            </div>

            <!-- Search Form -->
            <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-4xl mx-auto">
                <!-- Category Selection -->
                <div class="flex flex-wrap justify-center gap-4 mb-8">
                    <button onclick="selectCategory('rental')" id="btn-rental" class="category-btn px-6 py-3 rounded-lg font-semibold transition text-white" style="background-color: #1e3a8a;">
                        <i class="fas fa-car mr-2"></i>Rental Mobil
                    </button>
                    <button onclick="selectCategory('travel')" id="btn-travel" class="category-btn px-6 py-3 rounded-lg font-semibold transition text-gray-700 hover:bg-gray-300" style="background-color: #e5e7eb;">
                        <i class="fas fa-bus mr-2"></i>Travel
                    </button>
                </div>

                <!-- Search Fields -->
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">
                            <i class="fas fa-calendar text-blue-900 mr-2"></i>Tanggal
                        </label>
                        <input type="date" id="date" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-900">
                    </div>
                    <div id="time-field">
                        <label class="block text-gray-700 font-medium mb-2">
                            <i class="fas fa-clock text-blue-900 mr-2"></i>Jam
                        </label>
                        <input type="time" id="time" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-900">
                    </div>
                    <div id="travel-origin-field" class="hidden">
                        <label class="block text-gray-700 font-medium mb-2">
                            <i class="fas fa-map-marker-alt text-blue-900 mr-2"></i>Kota Asal
                        </label>
                        <input type="text" id="travel-origin" placeholder="Contoh: Jakarta" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-900">
                    </div>
                    <div id="travel-destination-field" class="hidden">
                        <label class="block text-gray-700 font-medium mb-2">
                            <i class="fas fa-map-marker-alt text-blue-900 mr-2"></i>Kota Tujuan
                        </label>
                        <input type="text" id="travel-destination" placeholder="Contoh: Bandung" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-900">
                    </div>
                    <div id="duration-field">
                        <label class="block text-gray-700 font-medium mb-2">
                            <i class="fas fa-hourglass-half text-blue-900 mr-2"></i>Durasi Sewa
                        </label>
                        <select id="duration" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-900">
                            <option value="">Pilih durasi</option>
                            <option value="1">1 Jam</option>
                            <option value="2">2 Jam</option>
                            <option value="3">3 Jam</option>
                            <option value="4">4 Jam</option>
                            <option value="6">6 Jam</option>
                            <option value="8">8 Jam</option>
                            <option value="12">12 Jam</option>
                            <option value="24">24 Jam</option>
                            <option value="48">2 Hari</option>
                            <option value="72">3 Hari</option>
                            <option value="168">7 Hari</option>
                        </select>
                    </div>
                </div>

                <!-- Search Button -->
                <div class="mt-8 text-center">
                    <button onclick="searchService()" class="bg-blue-900 text-white px-12 py-4 rounded-lg font-semibold hover:bg-blue-800 transition text-lg">
                        <i class="fas fa-search mr-2"></i>Cari Sekarang
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Why Choose ASR GO?</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Experience the best travel services with our comprehensive platform designed for all your transportation needs.</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-blue-50 rounded-xl p-8 hover:shadow-lg transition">
                    <div class="bg-blue-900 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-users text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Multi-Role Platform</h3>
                    <p class="text-gray-600">Admin, Mitra, Driver, and Customer roles - everyone has their dedicated dashboard for seamless operations.</p>
                </div>
                <div class="bg-blue-50 rounded-xl p-8 hover:shadow-lg transition">
                    <div class="bg-blue-900 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-mobile-alt text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">User-Friendly Interface</h3>
                    <p class="text-gray-600">Intuitive design built with Laravel and Tailwind CSS for smooth navigation on desktop and mobile devices.</p>
                </div>
                <div class="bg-blue-50 rounded-xl p-8 hover:shadow-lg transition">
                    <div class="bg-blue-900 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-lock text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Secure Authentication</h3>
                    <p class="text-gray-600">Role-based access control ensures users can only access authorized sections and data.</p>
                </div>
                <div class="bg-blue-50 rounded-xl p-8 hover:shadow-lg transition">
                    <div class="bg-blue-900 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-bolt text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Fast Booking System</h3>
                    <p class="text-gray-600">Quick and efficient booking process with real-time availability and instant confirmation.</p>
                </div>
                <div class="bg-blue-50 rounded-xl p-8 hover:shadow-lg transition">
                    <div class="bg-blue-900 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-headset text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">24/7 Support</h3>
                    <p class="text-gray-600">Round-the-clock customer support to assist you with any queries or issues.</p>
                </div>
                <div class="bg-blue-50 rounded-xl p-8 hover:shadow-lg transition">
                    <div class="bg-blue-900 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-dollar-sign text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Competitive Pricing</h3>
                    <p class="text-gray-600">Affordable rates with transparent pricing - no hidden charges or surprises.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-20 bg-blue-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">About ASR GO</h2>
                    <p class="text-blue-100 mb-6">
                        ASR GO is a modern travel application designed to make your journeys comfortable, safe, and convenient. Our platform connects customers with trusted drivers and partners, ensuring a seamless travel experience.
                    </p>
                    <p class="text-blue-100 mb-6">
                        With our multi-role system, we provide dedicated dashboards for admins, partners, drivers, and customers, each tailored to their specific needs and responsibilities.
                    </p>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="text-center">
                            <div class="text-4xl font-bold text-white mb-2">4+</div>
                            <div class="text-blue-100">User Roles</div>
                        </div>
                        <div class="text-center">
                            <div class="text-4xl font-bold text-white mb-2">24/7</div>
                            <div class="text-blue-100">Availability</div>
                        </div>
                        <div class="text-center">
                            <div class="text-4xl font-bold text-white mb-2">100%</div>
                            <div class="text-blue-100">Secure</div>
                        </div>
                        <div class="text-center">
                            <div class="text-4xl font-bold text-white mb-2">Fast</div>
                            <div class="text-blue-100">Booking</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl p-8">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">Our Roles</h3>
                    <div class="space-y-4">
                        <div class="flex items-start space-x-4">
                            <div class="bg-blue-100 p-3 rounded-lg">
                                <i class="fas fa-user-shield text-blue-900"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800">Admin</h4>
                                <p class="text-gray-600 text-sm">Full system control and management</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="bg-blue-100 p-3 rounded-lg">
                                <i class="fas fa-handshake text-blue-900"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800">Mitra</h4>
                                <p class="text-gray-600 text-sm">Partner management and services</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="bg-blue-100 p-3 rounded-lg">
                                <i class="fas fa-car text-blue-900"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800">Driver</h4>
                                <p class="text-gray-600 text-sm">Trip management and scheduling</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="bg-blue-100 p-3 rounded-lg">
                                <i class="fas fa-user text-blue-900"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800">Customer</h4>
                                <p class="text-gray-600 text-sm">Booking and travel management</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Contact Us</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
            </div>
            <div class="grid md:grid-cols-2 gap-12">
                <div>
                    <div class="space-y-6">
                        <div class="flex items-start space-x-4">
                            <div class="bg-blue-100 p-4 rounded-lg">
                                <i class="fas fa-map-marker-alt text-blue-900 text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800">Address</h4>
                                <p class="text-gray-600">123 Travel Street, Jakarta, Indonesia</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="bg-blue-100 p-4 rounded-lg">
                                <i class="fas fa-phone text-blue-900 text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800">Phone</h4>
                                <p class="text-gray-600">+62 812 3456 7890</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="bg-blue-100 p-4 rounded-lg">
                                <i class="fas fa-envelope text-blue-900 text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800">Email</h4>
                                <p class="text-gray-600">info@asrgo.test</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-blue-50 rounded-xl p-8">
                    <form class="space-y-6">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Name</label>
                            <input type="text" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-900" placeholder="Your name">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Email</label>
                            <input type="email" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-900" placeholder="Your email">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Message</label>
                            <textarea rows="4" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-900" placeholder="Your message"></textarea>
                        </div>
                        <button type="submit" class="w-full bg-blue-900 text-white py-3 rounded-lg font-semibold hover:bg-blue-800 transition">
                            Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-2xl font-bold text-blue-300 mb-4">ASR GO</h3>
                    <p class="text-gray-400">Your trusted travel partner for comfortable and reliable journeys.</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#home" class="hover:text-blue-300 transition">Home</a></li>
                        <li><a href="#features" class="hover:text-blue-300 transition">Features</a></li>
                        <li><a href="#about" class="hover:text-blue-300 transition">About</a></li>
                        <li><a href="#contact" class="hover:text-blue-300 transition">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Roles</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-blue-300 transition">Admin</a></li>
                        <li><a href="#" class="hover:text-blue-300 transition">Mitra</a></li>
                        <li><a href="#" class="hover:text-blue-300 transition">Driver</a></li>
                        <li><a href="#" class="hover:text-blue-300 transition">Customer</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Follow Us</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="bg-blue-900 w-10 h-10 rounded-full flex items-center justify-center hover:bg-blue-800 transition">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="bg-blue-900 w-10 h-10 rounded-full flex items-center justify-center hover:bg-blue-800 transition">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="bg-blue-900 w-10 h-10 rounded-full flex items-center justify-center hover:bg-blue-800 transition">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="bg-blue-900 w-10 h-10 rounded-full flex items-center justify-center hover:bg-blue-800 transition">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2026 ASR GO. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Category selection
        let selectedCategory = 'rental';

        function selectCategory(category) {
            selectedCategory = category;

            // Reset all buttons
            document.querySelectorAll('.category-btn').forEach(btn => {
                btn.style.backgroundColor = '#e5e7eb';
                btn.classList.remove('text-white');
                btn.classList.add('text-gray-700');
                btn.classList.remove('hover:bg-gray-300');
                btn.classList.add('hover:bg-gray-300');
            });

            // Highlight selected button
            const selectedBtn = document.getElementById(`btn-${category}`);
            selectedBtn.style.backgroundColor = '#1e3a8a';
            selectedBtn.classList.remove('text-gray-700');
            selectedBtn.classList.add('text-white');
            selectedBtn.classList.remove('hover:bg-gray-300');

            // Show/hide fields based on category
            const durationField = document.getElementById('duration-field');
            const travelOriginField = document.getElementById('travel-origin-field');
            const travelDestinationField = document.getElementById('travel-destination-field');

            if (category === 'rental') {
                durationField.classList.remove('hidden');
                travelOriginField.classList.add('hidden');
                travelDestinationField.classList.add('hidden');
            } else {
                durationField.classList.add('hidden');
                travelOriginField.classList.remove('hidden');
                travelDestinationField.classList.remove('hidden');
            }
        }

        // Search function
        function searchService() {
            const date = document.getElementById('date').value;
            const time = document.getElementById('time').value;
            const duration = document.getElementById('duration').value;
            const travelOrigin = document.getElementById('travel-origin').value;
            const travelDestination = document.getElementById('travel-destination').value;

            if (!date) { alert('Silakan pilih tanggal'); return; }
            if (!time) { alert('Silakan pilih jam jemput'); return; }
            if (selectedCategory === 'rental' && !duration) { alert('Silakan pilih durasi sewa'); return; }
            if (selectedCategory === 'travel' && (!travelOrigin || !travelDestination)) { alert('Silakan isi kota asal dan kota tujuan'); return; }

            const searchData = {
                category: selectedCategory, date, time,
                duration: selectedCategory === 'rental' ? duration : null,
                travelOrigin: selectedCategory === 'travel' ? travelOrigin : null,
                travelDestination: selectedCategory === 'travel' ? travelDestination : null
            };
            console.log('Search Data:', searchData);
            alert(`Pencarian ${selectedCategory.toUpperCase()} berhasil!\n\nTanggal: ${date}\nJam: ${time}\n${selectedCategory === 'rental' ? 'Durasi: ' + duration + ' jam' : ''}${selectedCategory === 'travel' ? 'Kota Asal: ' + travelOrigin + '\nKota Tujuan: ' + travelDestination : ''}\n\n(Dalam implementasi nyata, ini akan mengarahkan ke halaman hasil pencarian)`);
        }

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Set default date to today
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('date').value = today;
        });
    </script>
</body>

</html>