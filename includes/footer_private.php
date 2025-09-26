</main>
<footer class="private-footer">
    <div class="container">
        <div class="footer-content">
            <p class="copyright">&copy; 2025 CCP Ride Sharing. All rights reserved.</p>
            <nav class="footer-nav">
                <ul>
                    <li><a href="/privacy">Privacy Policy</a></li>
                    <li><a href="/terms">Terms of Service</a></li>
                    <li><a href="/contact">Contact Us</a></li>
                </ul>
            </nav>
        </div>
    </div>
</footer>

<style>
/* Reset default margins and ensure box-sizing */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Footer styling */
.private-footer {
    background-color: #6a4981; /* Deep purple from palette */
    color: #f7c8d9; /* Light pink for text */
    padding: 2rem 0;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 0.875rem; /* 14px for readability */
    line-height: 1.5;
}

/* Container for consistent width */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1.5rem;
}

/* Flexbox for footer content */
.footer-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

/* Copyright text */
.copyright {
    color: #f7c8d9; /* Light pink */
    font-weight: 400;
}

/* Footer navigation */
.footer-nav ul {
    list-style: none;
    display: flex;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.footer-nav a {
    color: #f7c8d9; /* Light pink */
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s ease;
}

.footer-nav a:hover {
    color: #7d5a9b; /* Mid-tone purple for hover */
}

/* Responsive design */
@media (max-width: 768px) {
    .footer-content {
        flex-direction: column;
        text-align: center;
    }

    .footer-nav ul {
        justify-content: center;
    }
}
</style>
</body>
</html>