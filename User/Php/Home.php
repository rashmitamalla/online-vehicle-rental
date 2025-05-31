<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>
    Home | Vehicle Rental
  </title>
  <link rel="stylesheet" href="../../user/Css/style.css">



  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
    crossorigin="anonymous"
    referrerpolicy="no-referrer" />

</head>

<style>
  .vehicle_container {
    max-width: 1200px;
    margin: auto;
    padding: 40px 20px;
    text-align: center;
  }

  h1 {
    font-size: 36px;
    margin-bottom: 10px;
    color: #1d3557;
  }

  p.subtitle {
    font-size: 18px;
    margin-bottom: 40px;
    color: #555;
  }

  .categories {
    display: flex;
    justify-content: space-around;
    flex-wrap: wrap;
    gap: 20px;
  }

  .category-card {
    background-color: #fff;
    border-radius: 10px;
    width: 220px;
    padding: 15px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
  }

  .category-card:hover {
    transform: translateY(-5px);
  }

  .category-card img {
    width: 100%;
    height: auto;
    border-radius: 8px;
  }

  .category-card h3 {
    margin-top: 15px;
    font-size: 18px;
    color: #1d3557;
  }

  .book-btn {
    margin-top: 50px;
    background-color: #e63946;
    color: white;
    border: none;
    padding: 14px 30px;
    font-size: 18px;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s ease;
  }

  .book-btn:hover {
    background-color: #d62828;
  }

  @media (max-width: 768px) {
    .categories {
      flex-direction: column;
      align-items: center;
    }
  }
</style>

<body>
  <?php
  include "Header.php";
  ?>
  <!-- container for the hero section  -->
  <div class="slider-container">

    <div class="slider">
      <img src="../../Image/bus.webp" class="slide active" alt="Image 1">
      <img src="../../Image/images.jpg" class="slide" alt="Image 2">
      <img src="../../Image/jeep7.jpg" class="slide" alt="Image 3">
      <img src="../../Image/image.png" class="slide" alt="Image 4">
    </div>
    <div class="overlay"></div>
    <div class="dots-container">
      <span class="dot active-dot" data-slide="0"></span>
      <span class="dot" data-slide="1"></span>
      <span class="dot" data-slide="2"></span>
      <span class="dot" data-slide="3"></span>
    </div>
  </div>

  <div class="container">

    <h2>Discover the Nepal's largest car rental marketplace</h2>

    <div class="features">
      <div class="feature">
        <div class="feature-icon">∞</div>
        <h3>Endless options</h3>
        <p>Choose from thousands of vehicles you won’t find anywhere else. Choose it and get picked up where you want it.</p>
      </div>

      <div class="feature">
        <div class="feature-icon">🎧</div>
        <h3>24/7 customer support</h3>
        <p>Rest easy knowing that everyone in the Sajilo rental community is screened, and 24/7 customer support and roadside assistance are just a click away.</p>
      </div>

      <div class="feature">
        <div class="feature-icon">🛡️</div>
        <h3>Go for trip confidently</h3>
        <p>Go for trip confidently with your choice of protection plans — all plans include varying levels of liability insurance provided through Sajilo Rental's Insurance Agency.</p>
      </div>
    </div>

    <div class="button-container">
      <button class="button" onclick="location.href='Contact.php';">Book the perfect car</button>

    </div>
  </div>


  <div class="vehicle_container">
    <h1>Categories</h1>
    <p class="subtitle">Choose from thousands of cars in over all major cities across the Country.</p>

    <div class="categories">
      <div class="category-card">
        <a href="Listing.php?category=Car" class="vcard">
          <img src="../../Image/car3.jpg" alt="Car" />
          <div class="vcard-d">
            <h3>Car</h3>
          </div>
        </a>
      </div>

      <div class="category-card">
        <a href="Listing.php?category=Sumo" class="vcard">
          <img src="../../Image/sumo1.jpg" alt="Sumo" />
          <div class="vcard-d">
            <h3>Sumo</h3>
          </div>
        </a>
      </div>

      <div class="category-card">
        <a href="Listing.php?category=Hiace" class="vcard">
          <img src="../../Image/hiace1.jpg" alt="Hiace" />
          <div class="vcard-d">
            <h3>Hiace</h3>
          </div>
        </a>
      </div>

      <div class="category-card">
        <a href="Listing.php?category=Bus" class="vcard">
          <img src="../../Image/bus1.jpg" alt="Bus" />
          <div class="vcard-d">
            <h3>Bus</h3>
          </div>
        </a>
      </div>
    </div>
  </div>



  <?php
  include "footer.php";
  ?>
  <script>
    let currentSlide = 0;
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.dot');

    function showSlide(index) {
      slides.forEach((slide, i) => {
        slide.classList.remove('active');
        if (i === index) slide.classList.add('active');
      });

      dots.forEach(dot => dot.classList.remove('active-dot'));
      dots[index].classList.add('active-dot');
    }

    dots.forEach(dot => {
      dot.addEventListener('click', () => {
        const index = parseInt(dot.getAttribute('data-slide'));
        currentSlide = index;
        showSlide(currentSlide);
      });
    });

    setInterval(() => {
      currentSlide = (currentSlide + 1) % slides.length;
      showSlide(currentSlide);
    }, 3000);
  </script>

</body>

</html>