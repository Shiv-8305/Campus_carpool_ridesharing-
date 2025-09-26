<?php
include '../includes/header_public.php';
?>
<section class="landing-container" style="min-height:85vh;">
    <div class="landing-image">
        <img src="../assets/images/Main.png" alt="Ride Sharing Illustration" />
    </div>
    <div class="welcome-text">
        <h1>Welcome to CampusCarPool Ride Sharing</h1>
        <p>Safe, convenient rides for students & staff.</p>
    </div>
</section>

<!-- Floating Driver Login button -->
<a href="driver_login.php" class="floating-driver-login-btn">
    Driver Login
</a>

<style>
/* Your existing theme may already set up landing-container, landing-image, etc. */
.landing-container {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    padding-top: 3rem;
}
.landing-image img {
    max-width: 370px;
    width: 96vw;
    height: auto;
    margin-right: 2.7rem;
    border-radius: 22px;
    box-shadow: 0 8px 42px #b096dd49;
    background: #f9e6ff;
}

.welcome-text h1 {
    font-size: 2.6rem;
    color: #8d5ec9;
    margin-bottom: 0.5rem;
    font-weight: 800;
}

.welcome-text p {
    color: #8a65af;
    font-size: 1.24rem;
    font-weight: 500;
    margin-bottom: 2rem;
}

/* Fixed Driver Login button */
.floating-driver-login-btn {
    position: fixed;
    right: 2.5rem;
    bottom: 2.5rem;
    background: #7f54c0d9
    color: #7f54c0d9 ;
    font-size: 1.11rem;
    font-weight: 700;
    padding: 15px 34px;
    border-radius: 34px;
    border: none;
    outline: none;
    text-decoration: none;
    box-shadow: 0 6px 22px rgba(131, 74, 193, 0.11);
    z-index: 1001;
    transition: background .25s, box-shadow .22s;
}
.floating-driver-login-btn:hover, .floating-driver-login-btn:focus {
    background: linear-gradient(120deg, #63329e 55%, #41ba8e 95%);
    color: #fff;
    box-shadow: 0 8px 40px #7fd9b497;
}

@media(max-width:700px){
    .floating-driver-login-btn{
        padding: 13px 15vw;
        right: 0.8rem;
        bottom: 1.3rem;
        font-size: 1.07rem;
    }
    .landing-image img { margin: 0 auto 2.2rem auto;}
    .landing-container{flex-direction:column;}
}
</style>
<?php
include '../includes/footer_public.php';
?>
