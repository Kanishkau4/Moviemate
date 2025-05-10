<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - MovieMate</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        /* Hero Section with Parallax */
        .hero {
            height: 100vh;
            position: relative;
            overflow: hidden;
            background: black;
        }

        .parallax-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 120%;
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), 
                       url('/api/placeholder/1920/1080');
            background-size: cover;
            background-position: center;
            transform: translateY(0);
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
            padding: 0 20px;
        }

        .hero-title {
            font-size: 4rem;
            font-weight: bold;
            margin-bottom: 1rem;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 1s ease forwards;
        }

        .hero-subtitle {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 1s ease 0.3s forwards;
        }

        .scroll-indicator {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            animation: bounce 2s infinite;
            z-index: 2;
            color: white;
        }

        /* Timeline Section */
        .timeline {
            padding: 6rem 5%;
            background: #fff;
        }

        .timeline-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 4rem;
        }

        .timeline-container {
            max-width: 1000px;
            margin: 0 auto;
            position: relative;
        }

        .timeline-container::after {
            content: '';
            position: absolute;
            width: 2px;
            background: #1a73e8;
            top: 0;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
        }

        .timeline-item {
            padding: 10px 40px;
            position: relative;
            width: 50%;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s ease;
        }

        .timeline-item.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .timeline-item:nth-child(odd) {
            left: 0;
            text-align: right;
        }

        .timeline-item:nth-child(even) {
            left: 50%;
        }

        .timeline-content {
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .timeline-dot {
            width: 20px;
            height: 20px;
            background: #1a73e8;
            border-radius: 50%;
            position: absolute;
            top: 20px;
            right: -50px;
            transform: translateX(-50%);
        }

        .timeline-item:nth-child(even) .timeline-dot {
            left: -10px;
        }

        /* Features Section */
        .features {
            padding: 6rem 5%;
            background: #f8f9fa;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: #1a73e8;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 1.5rem;
        }

        /* Team Section */
        .team {
            padding: 6rem 5%;
            background: white;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .team-member {
            text-align: center;
            position: relative;
            overflow: hidden;
            border-radius: 10px;
        }

        .team-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 10px;
            transition: transform 0.3s ease;
        }

        .team-info {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            color: white;
            padding: 1.5rem;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }

        .team-member:hover .team-info {
            transform: translateY(0);
        }

        .team-member:hover .team-image {
            transform: scale(1.1);
        }

        /* Gallery Section */
        .gallery {
            padding: 6rem 5%;
            background: #f8f9fa;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .gallery-item {
            position: relative;
            height: 300px;
            overflow: hidden;
            border-radius: 10px;
            cursor: pointer;
        }

        .gallery-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .gallery-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .gallery-item:hover .gallery-image {
            transform: scale(1.1);
        }

        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }

        .gallery-text {
            color: white;
            text-align: center;
            padding: 1rem;
        }

        /* Testimonials Section */
        .testimonials {
            padding: 6rem 5%;
            background: white;
        }

        .testimonial-slider {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
        }

        .testimonial-item {
            text-align: center;
            padding: 2rem;
            display: none;
        }

        .testimonial-item.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        .testimonial-text {
            font-size: 1.2rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            font-style: italic;
        }

        .testimonial-author {
            font-weight: bold;
            color: #1a73e8;
        }

        .testimonial-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            font-size: 2rem;
            color: #1a73e8;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .testimonial-nav:hover {
            color: #1557b0;
        }

        .testimonial-prev {
            left: -50px;
        }

        .testimonial-next {
            right: -50px;
        }

        /* Contact Section with Map */
        .contact {
            padding: 6rem 5%;
            background: #f8f9fa;
        }

        .contact-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .map-container {
            border-radius: 10px;
            overflow: hidden;
            height: 400px;
        }

        .contact-info {
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .contact-icon {
            width: 40px;
            height: 40px;
            background: #1a73e8;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-30px);
            }
            60% {
                transform: translateY(-15px);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .team-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .contact-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 3rem;
            }

            .timeline-container::after {
                left: 31px;
            }

            .timeline-item {
                width: 100%;
                left: 0;
                padding-left: 70px;
                padding-right: 25px;
                text-align: left;
            }

            .timeline-item:nth-child(even) {
                left: 0;
            }

            .timeline-dot {
                left: 21px;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .team-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero">
        <div class="parallax-bg"></div>
        <div class="hero-content">
            <h1 class="hero-title">MovieMate</h1>
            <p class="hero-subtitle">Your Premier Cinema Experience in Matara, Sri Lanka</p>
        </div>
        <div class="scroll-indicator">
            <i class="fas fa-chevron-down"></i>
        </div>
    </section>

    <!-- Timeline Section -->
    <section class="timeline">
        <h2 class="timeline-title">Our Journey</h2>
        <div class="timeline-container">
            <div class="timeline-item">
                <div class="timeline-content">
                    <h3>2008</h3>
                    <p>Founded in Matara with a vision to bring premium cinema experience to Southern Sri Lanka</p>
                </div>
                <div class="timeline-dot"></div>
            </div>
            <div class="timeline-item">
                <div class="timeline-content">
                    <h3>2012</h3>
                    <p>Expanded to include three state-of-the-art screens with Dolby sound systems</p>
                </div>
                <div class="timeline-dot"></div>
            </div>
            <div class="timeline-item">
                <div class="timeline-content">
                    <h3>2015</h3>
                    <p>Introduced IMAX technology and premium seating options</p>
                </div>
                <div class="timeline-dot"></div>
            </div>
            <div class="timeline-item">
                <div class="timeline-content">
                    <h3>2020</h3>
                    <p>Launched online booking system and mobile app for seamless movie experience</p>
                </div>
                <div class="timeline-dot"></div>
            </div>
            <div class="timeline-item">
                <div class="timeline-content">
                    <h3>2023</h3>
                    <p>Awarded Best Cinema Experience in Southern Province</p>
                </div>
                <div class="timeline-dot"></div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <h2 class="timeline-title">Why Choose Us</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-film"></i>
                </div>
                <h3>Latest Technology</h3>
                <p>Experience movies in stunning 4K resolution and Dolby Atmos sound</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-couch"></i>
                </div>
                <h3>Premium Comfort</h3>
                <p>Relax in our luxurious reclining seats with ample legroom</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3>Easy Booking</h3>
                <p>Book your tickets instantly through our mobile app or website</p>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team">
        <h2 class="timeline-title">Our Team</h2>
        <div class="team-grid">
            <div class="team-member">
                <img src="Images/team1.jpeg" alt="Team Member" class="team-image">
                <div class="team-info">
                    <h3>Kanishka Udayanga</h3>
                    <p>General Manager</p>
                </div>
            </div>
            <div class="team-member">
                <img src="Images/team2.jpg" alt="Team Member" class="team-image">
                <div class="team-info">
                    <h3>Adeepa Shavinda</h3>
                    <p>Operations Director</p>
                </div>
            </div>
            <div class="team-member">
                <img src="Images/team3.jpg" alt="Team Member" class="team-image">
                <div class="team-info">
                    <h3>Nimesh Sathsara</h3>
                    <p>Technical Manager</p>
                </div>
            </div>
            <div class="team-member">
                <img src="Images/team4.jpg" alt="Team Member" class="team-image">
                <div class="team-info">
                    <h3>Tharushi Sewmini</h3>
                    <p>Customer Relations</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section class="gallery">
        <h2 class="timeline-title">Our Cinema</h2>
        <div class="gallery-grid">
            <div class="gallery-item">
                <img src="Images/m3.jpg" alt="Cinema Interior" class="gallery-image">
                <div class="gallery-overlay">
                    <div class="gallery-text">
                        <h3>IMAX Theatre</h3>
                        <p>State-of-the-art IMAX screen</p>
                    </div>
                </div>
            </div>
            <div class="gallery-item">
                <img src="Images/m2.jpg" alt="Premium Lounge" class="gallery-image">
                <div class="gallery-overlay">
                    <div class="gallery-text">
                        <h3>Premium Lounge</h3>
                        <p>Exclusive waiting area for premium members</p>
                    </div>
                </div>
            </div>
            <div class="gallery-item">
                <img src="Images/m4.jpg" alt="Concession Stand" class="gallery-image">
                <div class="gallery-overlay">
                    <div class="gallery-text">
                        <h3>Food Court</h3>
                        <p>Wide variety of snacks and beverages</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <h2 class="timeline-title">What Our Customers Say</h2>
        <div class="testimonial-slider">
            <div class="testimonial-item active">
                <p class="testimonial-text">"Best cinema experience in Southern Sri Lanka. The IMAX screen is absolutely amazing!"</p>
                <p class="testimonial-author">- Chamodha D.</p>
            </div>
            <div class="testimonial-item">
                <p class="testimonial-text">"The online booking system is so convenient. Never had any issues!"</p>
                <p class="testimonial-author">- Lahiru J.</p>
            </div>
            <div class="testimonial-item">
                <p class="testimonial-text">"Premium seating is worth every rupee. Such a comfortable experience!"</p>
                <p class="testimonial-author">- Ranjan K.</p>
            </div>
            <i class="fas fa-chevron-left testimonial-nav testimonial-prev"></i>
            <i class="fas fa-chevron-right testimonial-nav testimonial-next"></i>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact">
        <h2 class="timeline-title">Contact Us</h2>
        <div class="contact-container">
            <div class="contact-info">
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <p>123 Cinema Road, Matara, Sri Lanka</p>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <p>+94 41 222 3333</p>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <p>info@moviemate.lk</p>
                </div>
            </div>
            <div class="map-container">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3960.3797661217575!2d80.543549615401!3d5.968749037927481!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae2a5e5d9bcd813%3A0x64d1a4d45fefedc5!2sMatara%2C%20Sri%20Lanka!5e0!3m2!1sen!2sus!4v1641422121898!5m2!1sen!2sus
" width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
    </section>

    <script>
        // Parallax Effect
        window.addEventListener('scroll', () => {
            const parallax = document.querySelector('.parallax-bg');
            const scrolled = window.pageYOffset;
            parallax.style.transform = `translateY(${scrolled * 0.5}px)`;
        });

        // Timeline Animation
        const timelineItems = document.querySelectorAll('.timeline-item');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        });

        timelineItems.forEach(item => observer.observe(item));

        // Testimonial Slider
        const testimonials = document.querySelectorAll('.testimonial-item');
        let currentTestimonial = 0;

        function showTestimonial(index) {
            testimonials.forEach(item => item.classList.remove('active'));
            testimonials[index].classList.add('active');
        }

        document.querySelector('.testimonial-next').addEventListener('click', () => {
            currentTestimonial = (currentTestimonial + 1) % testimonials.length;
            showTestimonial(currentTestimonial);
        });

        document.querySelector('.testimonial-prev').addEventListener('click', () => {
            currentTestimonial = (currentTestimonial - 1 + testimonials.length) % testimonials.length;
            showTestimonial(currentTestimonial);
        });
    </script>
</body>
</html>