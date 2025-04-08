// Wait for DOM to load
document.addEventListener('DOMContentLoaded', function() {
    // Cart Counter Update
    function updateCart() {
        fetch('get_cart_count.php')
            .then(response => response.json())
            .then(data => {
                document.querySelector('.cart-icon .counter').textContent = data.count;
            })
            .catch(error => console.error('Error:', error));
    }

    // Wishlist Counter Update
    function updateWishlist() {
        fetch('get_wishlist_count.php')
            .then(response => response.json())
            .then(data => {
                document.querySelector('.wishlist-icon .counter').textContent = data.count;
            })
            .catch(error => console.error('Error:', error));
    }

    // Search Functionality
    const searchInput = document.querySelector('.search-box input');
    let searchTimeout;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const searchTerm = this.value;
            if (searchTerm.length >= 3) {
                fetch(`search_products.php?term=${encodeURIComponent(searchTerm)}`)
                    .then(response => response.json())
                    .then(data => {
                        // Update search results
                        console.log('Search results:', data);
                    })
                    .catch(error => console.error('Error:', error));
            }
        }, 300);
    });

    // Initialize features
    updateCart();
    updateWishlist();
});
// slider script

document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.slide');
    const navContainer = document.querySelector('.slider-nav');
    const prevButton = document.querySelector('.prev');
    const nextButton = document.querySelector('.next');
    let currentSlide = 0;

    // Create navigation dots
    slides.forEach((_, index) => {
        const dot = document.createElement('button');
        dot.addEventListener('click', () => goToSlide(index));
        navContainer.appendChild(dot);
    });

    const dots = navContainer.querySelectorAll('button');

    function updateSlider() {
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        
        slides[currentSlide].classList.add('active');
        dots[currentSlide].classList.add('active');
    }

    function goToSlide(index) {
        currentSlide = index;
        updateSlider();
    }

    function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        updateSlider();
    }

    function prevSlide() {
        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
        updateSlider();
    }

    // Add click events to arrows
    nextButton.addEventListener('click', nextSlide);
    prevButton.addEventListener('click', prevSlide);

    // Auto-advance slides every 5 seconds
    setInterval(nextSlide, 5000);

    // Initialize the slider
    updateSlider();
});