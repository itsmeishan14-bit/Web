// ============  DOM References  ============
const loginForm = document.getElementById("loginForm");
const registerForm = document.getElementById("registerForm");
const toggleWrapper = document.getElementById("toggleWrapper");
const loginToggle = document.getElementById("loginToggle");
const registerToggle = document.getElementById("registerToggle");
const goToRegister = document.getElementById("goToRegister");
const goToLogin = document.getElementById("goToLogin");

// ============  Toggle Logic  ============
function showLogin() {
    toggleWrapper.classList.remove("register");
    loginToggle.classList.add("active");
    registerToggle.classList.remove("active");
    loginForm.classList.add("form-active");
    registerForm.classList.remove("form-active");
}

function showRegister() {
    toggleWrapper.classList.add("register");
    registerToggle.classList.add("active");
    loginToggle.classList.remove("active");
    registerForm.classList.add("form-active");
    loginForm.classList.remove("form-active");
}

loginToggle.addEventListener("click", showLogin);
registerToggle.addEventListener("click", showRegister);
goToRegister.addEventListener("click", (e) => { e.preventDefault(); showRegister(); });
goToLogin.addEventListener("click", (e) => { e.preventDefault(); showLogin(); });

// ============  Country / State / City Data  ============
// Nested structure so we can populate states and then cities
const countryStateCityMap = {
    "United States": {
        "California": ["Los Angeles", "San Francisco", "San Diego"],
        "Texas": ["Houston", "Dallas", "Austin"],
        "New York": ["New York City", "Buffalo", "Rochester"],
        "Florida": ["Miami", "Orlando", "Tampa"],
        "Illinois": ["Chicago", "Springfield", "Naperville"]
    },
    "India": {
        "Maharashtra": ["Mumbai", "Pune", "Nagpur"],
        "Karnataka": ["Bengaluru", "Mysore", "Mangalore"],
        "Tamil Nadu": ["Chennai", "Coimbatore", "Madurai"],
        "Uttar Pradesh": ["Lucknow", "Kanpur", "Agra"]
    },
    "United Kingdom": {
        "England": ["London", "Manchester", "Birmingham"],
        "Scotland": ["Edinburgh", "Glasgow", "Aberdeen"],
        "Wales": ["Cardiff", "Swansea"],
        "Northern Ireland": ["Belfast", "Derry"]
    },
    "Canada": {
        "Ontario": ["Toronto", "Ottawa", "Hamilton"],
        "Quebec": ["Montreal", "Quebec City"],
        "British Columbia": ["Vancouver", "Victoria"]
    },
    "Australia": {
        "New South Wales": ["Sydney", "Newcastle"],
        "Victoria": ["Melbourne", "Geelong"],
        "Queensland": ["Brisbane", "Gold Coast"]
    },
    "Germany": {
        "Bavaria": ["Munich", "Nuremberg"],
        "Berlin": ["Berlin"],
        "Hamburg": ["Hamburg"]
    },
    "France": {
        "Île-de-France": ["Paris"],
        "Provence-Alpes-Côte d'Azur": ["Nice", "Marseille"]
    },
    "Japan": {
        "Tokyo": ["Tokyo"],
        "Osaka": ["Osaka"]
    },
    "Brazil": {
        "São Paulo": ["São Paulo"],
        "Rio de Janeiro": ["Rio de Janeiro"]
    },
    "South Korea": {
        "Seoul": ["Seoul"],
        "Busan": ["Busan"]
    },
    "Mexico": {
        "Jalisco": ["Guadalajara", "Puerto Vallarta"],
        "Mexico City": ["Coyoacán", "Polanco"]
    },
    "Spain": {
        "Madrid": ["Madrid"],
        "Catalonia": ["Barcelona", "Girona"]
    },
    "China": {
        "Guangdong": ["Guangzhou", "Shenzhen"],
        "Beijing": ["Beijing"]
    },
    "Russia": {
        "Moscow": ["Moscow"],
        "Saint Petersburg": ["Saint Petersburg"]
    }
};

const countrySelect = document.getElementById("country");
const stateSelect = document.getElementById("state");
const citySelect = document.getElementById("city");

// mapping of flags for display in selects
const countryFlagMap = {
    "United States": "🇺🇸",
    "India": "🇮🇳",
    "United Kingdom": "🇬🇧",
    "Canada": "🇨🇦",
    "Australia": "🇦🇺",
    "Germany": "🇩🇪",
    "France": "🇫🇷",
    "Japan": "🇯🇵",
    "Brazil": "🇧🇷",
    "South Korea": "🇰🇷",
    "Mexico": "🇲🇽",
    "Spain": "🇪🇸",
    "China": "🇨🇳",
    "Russia": "🇷🇺"
};

const phoneCountryList = [
    { code: "+1", flag: "🇺🇸" },
    { code: "+91", flag: "🇮🇳" },
    { code: "+44", flag: "🇬🇧" },
    { code: "+61", flag: "🇦🇺" },
    { code: "+49", flag: "🇩🇪" },
    { code: "+81", flag: "🇯🇵" },
    { code: "+55", flag: "🇧🇷" },
    { code: "+52", flag: "🇲🇽" },
    { code: "+34", flag: "🇪🇸" },
    { code: "+86", flag: "🇨🇳" },
    { code: "+7", flag: "🇷🇺" }
];

const phoneCountrySelect = document.getElementById("phoneCountry");

// Populate country dropdown with flags
Object.keys(countryStateCityMap).sort().forEach((c) => {
    const opt = document.createElement("option");
    opt.value = c;
    opt.textContent = `${countryFlagMap[c] || ""} ${c}`;
    countrySelect.appendChild(opt);
});

// when country changes, fill states and clear city
countrySelect.addEventListener("change", () => {
    const states = Object.keys(countryStateCityMap[countrySelect.value] || {});
    stateSelect.innerHTML = '<option value="" disabled selected>Select State</option>';
    states.forEach((s) => {
        const opt = document.createElement("option");
        opt.value = s;
        opt.textContent = s;
        stateSelect.appendChild(opt);
    });
    stateSelect.disabled = states.length === 0;

    // reset city
    citySelect.innerHTML = '<option value="" disabled selected>Select City</option>';
    citySelect.disabled = true;
});

// when state changes, fill cities
stateSelect.addEventListener("change", () => {
    const cities =
        countryStateCityMap[countrySelect.value]?.[stateSelect.value] || [];
    citySelect.innerHTML = '<option value="" disabled selected>Select City</option>';
    cities.forEach((ct) => {
        const opt = document.createElement("option");
        opt.value = ct;
        opt.textContent = ct;
        citySelect.appendChild(opt);
    });
    citySelect.disabled = cities.length === 0;
});

// populate phone-country codes with flags
phoneCountryList.forEach((item) => {
    const opt = document.createElement("option");
    opt.value = item.code;
    opt.textContent = `${item.flag} ${item.code}`;
    phoneCountrySelect.appendChild(opt);
});

// ============  Password Visibility Toggle  ============
document.querySelectorAll(".eye-btn").forEach((btn) => {
    btn.addEventListener("click", () => {
        const target = document.getElementById(btn.dataset.target);
        const isPassword = target.type === "password";
        target.type = isPassword ? "text" : "password";
        btn.querySelector(".eye-open").classList.toggle("hidden", !isPassword);
        btn.querySelector(".eye-closed").classList.toggle("hidden", isPassword);
    });
});

// ============  Password Strength Meter  ============
const regPassword = document.getElementById("regPassword");
const strengthFill = document.getElementById("strengthFill");
const strengthText = document.getElementById("strengthText");

function evaluateStrength(pw) {
    let score = 0;
    if (pw.length >= 8) score++;
    if (pw.length >= 12) score++;
    if (/[a-z]/.test(pw) && /[A-Z]/.test(pw)) score++;
    if (/\d/.test(pw)) score++;
    if (/[^a-zA-Z0-9]/.test(pw)) score++;
    return score; // 0‑5
}

const strengthConfig = [
    { label: "", color: "transparent", width: "0%" },
    { label: "Weak", color: "#ff6b6b", width: "20%" },
    { label: "Fair", color: "#fcc419", width: "40%" },
    { label: "Good", color: "#fab005", width: "60%" },
    { label: "Strong", color: "#51cf66", width: "80%" },
    { label: "Excellent", color: "#20c997", width: "100%" },
];

regPassword.addEventListener("input", () => {
    const s = evaluateStrength(regPassword.value);
    const cfg = strengthConfig[s];
    strengthFill.style.width = cfg.width;
    strengthFill.style.background = cfg.color;
    strengthText.textContent = cfg.label;
    strengthText.style.color = cfg.color;
    checkMatch();
});

// ============  Confirm Password Match  ============
const confirmPassword = document.getElementById("confirmPassword");
const matchMsg = document.getElementById("matchMsg");

function checkMatch() {
    if (!confirmPassword.value) { matchMsg.classList.add("hidden"); return; }
    matchMsg.classList.remove("hidden");
    if (confirmPassword.value === regPassword.value) {
        matchMsg.textContent = "✓ Passwords match";
        matchMsg.className = "match-msg success";
    } else {
        matchMsg.textContent = "✗ Passwords do not match";
        matchMsg.className = "match-msg error";
    }
}

confirmPassword.addEventListener("input", checkMatch);

// ============  Toast  ============
const toast = document.getElementById("toast");
const toastIcon = document.getElementById("toastIcon");
const toastMsg = document.getElementById("toastMsg");
let toastTimer = null;

function showToast(msg, type = "success") {
    clearTimeout(toastTimer);
    toastIcon.textContent = type === "success" ? "✅" : "⚠️";
    toastMsg.textContent = msg;
    toast.classList.remove("hidden");
    // trigger reflow for animation
    void toast.offsetWidth;
    toast.classList.add("show");
    toastTimer = setTimeout(() => {
        toast.classList.remove("show");
        setTimeout(() => toast.classList.add("hidden"), 300);
    }, 3500);
}

// ============  Form Submissions  ============
loginForm.addEventListener("submit", (e) => {
    e.preventDefault();
    showToast("Logged in successfully!");
    loginForm.reset();
});

registerForm.addEventListener("submit", (e) => {
    e.preventDefault();

    // Confirm password check
    if (regPassword.value !== confirmPassword.value) {
        showToast("Passwords do not match!", "error");
        return;
    }

    showToast("Account created successfully!");
    registerForm.reset();
    strengthFill.style.width = "0%";
    strengthText.textContent = "";
    matchMsg.classList.add("hidden");
    stateSelect.innerHTML = '<option value="" disabled selected>Select State</option>';
    stateSelect.disabled = true;
    citySelect.innerHTML = '<option value="" disabled selected>Select City</option>';
    citySelect.disabled = true;
    phoneCountrySelect.selectedIndex = 0;

    // Switch to login after short delay
    setTimeout(showLogin, 1200);
});
