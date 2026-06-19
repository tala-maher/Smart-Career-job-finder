document.addEventListener("DOMContentLoaded", () => {

    const currentLocation = location.pathname.split("/").slice(-1)[0];
    const navLinks = document.querySelectorAll(".nav-links a");

    navLinks.forEach(link => {
        const linkPath = link.getAttribute("href");
        if (linkPath === currentLocation) {
            link.classList.add("active");
        }
    });

    const heroTitle = document.querySelector(".hero h1, .jobs-header h1");

    if (heroTitle) {
        const text = heroTitle.innerText;
        heroTitle.innerText = "";
        let i = 0;

        const type = setInterval(() => {
            heroTitle.innerText += text[i];
            i++;
            if (i >= text.length) clearInterval(type);
        }, 50);
    }

    const aiBoxes = document.querySelectorAll(".ai-insight-box");
    aiBoxes.forEach((box, index) => {
        box.style.opacity = "0";
        box.style.transition = `opacity 0.8s ease ${index * 0.1}s`;
        setTimeout(() => { box.style.opacity = "1"; }, 100);
    });

    const hearts = document.querySelectorAll(".heart");
    hearts.forEach(heart => {
        heart.addEventListener("click", () => {
            heart.classList.toggle("liked");
            heart.classList.toggle("fa-regular");
            heart.classList.toggle("fa-solid");
            
            if (heart.classList.contains("liked")) {
                heart.style.color = "#ef4444";
                heart.style.transform = "scale(1.3)";
            } else {
                heart.style.color = "#94a3b8";
                heart.style.transform = "scale(1)";
            }
        });
    });

    const animateValue = (selector, suffix = "") => {
        const elements = document.querySelectorAll(selector);
        
        elements.forEach(el => {
            let textVal = el.innerText.replace("%", "").trim();
            let target = parseInt(textVal);
            if (isNaN(target) || target === 0) return;

            let start = 0;
            let duration = 1500; 
            let stepTime = Math.max(Math.floor(duration / target), 10);

            const timer = setInterval(() => {
                start++;
                el.innerText = start + suffix;
                if (start >= target) clearInterval(timer);
            }, stepTime);
        });
    };

    animateValue(".match, .match-score", "% Match");
    animateValue(".stat-box h3"); 

    const removeBtns = document.querySelectorAll(".remove-btn");
    removeBtns.forEach(btn => {
        btn.addEventListener("click", (e) => {
            const card = e.target.closest(".favorite-card");
            const jobTitle = card.querySelector("h2").innerText;

            if (confirm(`Are you sure you want to remove "${jobTitle}" from favorites?`)) {
                card.style.opacity = "0";
                card.style.transform = "scale(0.8)";
                setTimeout(() => {
                    card.remove();
                    const countBadge = document.querySelector(".fav-count");
                    if(countBadge) {
                        let currentCount = parseInt(countBadge.innerText);
                        countBadge.innerHTML = `<i class="fa-solid fa-heart"></i> ${currentCount - 1} Saved Jobs`;
                    }
                }, 400);
            }
        });
    });
});