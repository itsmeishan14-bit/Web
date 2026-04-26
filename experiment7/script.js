const initialProducts = [
    { id: "E201", name: "MacBook Pro M3", category: "Laptops", rating: 4.9, price: 1999 },
    { id: "E202", name: "iPhone 15 Pro", category: "Smartphones", rating: 4.8, price: 999 },
    { id: "E203", name: "Sony WH-1000XM5", category: "Audio", rating: 4.7, price: 349 },
    { id: "E204", name: "Samsung Odyssey G9", category: "Monitors", rating: 4.6, price: 1299 },
    { id: "E205", name: "Logitech MX Master 3S", category: "Peripherals", rating: 4.8, price: 99 },
    { id: "E206", name: "Dell XPS 15", category: "Laptops", rating: 4.5, price: 1599 },
    { id: "E207", name: "iPad Pro 12.9", category: "Tablets", rating: 4.8, price: 1099 },
    { id: "E208", name: "Bose QuietComfort", category: "Audio", rating: 4.6, price: 299 },
    { id: "E209", name: "Keychron Q60", category: "Peripherals", rating: 4.7, price: 160 },
    { id: "E210", name: "Nikon Z8", category: "Cameras", rating: 4.9, price: 3999 }
];

const initialStudents = [
    { id: "S101", name: "Rahul Sharma", age: 20, course: "Computer Science", marks: 85 },
    { id: "S102", name: "Ananya Iyer", age: 21, course: "Business", marks: 62 },
    { id: "S103", name: "Vikram Malhotra", age: 19, course: "Arts", marks: 45 },
    { id: "S104", name: "Sneha Patel", age: 22, course: "Computer Science", marks: 78 },
    { id: "S105", name: "Arjun Reddy", age: 20, course: "Business", marks: 55 },
    { id: "S106", name: "Priya Das", age: 21, course: "Arts", marks: 32 },
    { id: "S107", name: "Ishan Timalsina", age: 23, course: "Computer Science", marks: 100 },
    { id: "S108", name: "Zoya Khan", age: 20, course: "Business", marks: 89 },
    { id: "S109", name: "Kabir Singh", age: 22, course: "Arts", marks: 28 },
    { id: "S110", name: "David Luitel", age: 21, course: "Computer Science", marks: 72 }
];

let products = [...initialProducts];
let students = [...initialStudents];
let cart = [];
let currentMode = 'electronics'; // 'electronics' or 'students'
let currentView = [...products];

// DOM Elements
const tableBody = document.getElementById('tableBody');
const noData = document.getElementById('noData');
const searchId = document.getElementById('searchId');
const searchName = document.getElementById('searchName');

// Initialize
setMode('electronics');

// --- Core Functions ---

function updateTableHeaders() {
    const thead = document.querySelector('thead tr');
    if (currentMode === 'electronics') {
        thead.innerHTML = `
            <th>ID</th>
            <th>Product</th>
            <th>Category</th>
            <th>Rating</th>
            <th>Price</th>
            <th>Action</th>
        `;
    } else {
        thead.innerHTML = `
            <th>ID</th>
            <th>Student Name</th>
            <th>Age</th>
            <th>Course</th>
            <th>Marks</th>
            <th>Status</th>
        `;
    }
}

function renderTable(data) {
    updateTableHeaders();
    tableBody.innerHTML = '';

    if (data.length === 0) {
        noData.classList.remove('hidden');
    } else {
        noData.classList.add('hidden');

        data.forEach(item => {
            const row = document.createElement('tr');

            if (currentMode === 'electronics') {
                const ratingStars = '⭐'.repeat(Math.round(item.rating));
                const inCart = cart.some(cartItem => cartItem.id === item.id);
                row.innerHTML = `
                    <td><span class="product-id">${item.id}</span></td>
                    <td><span class="product-name">${item.name}</span></td>
                    <td><span class="category-tag">${item.category}</span></td>
                    <td>
                        <div class="rating-container">
                            <span class="rating-val">${item.rating}</span>
                            <span class="stars">${ratingStars}</span>
                        </div>
                    </td>
                    <td><span class="price-tag">$${item.price.toLocaleString()}</span></td>
                    <td>
                        <button class="add-cart-btn ${inCart ? 'in-cart' : ''}" onclick="toggleCart('${item.id}')">
                            ${inCart ? '✓ Added' : 'Add to Cart'}
                        </button>
                    </td>
                `;
            } else {
                const statusClass = item.marks >= 35 ? 'status-passed' : 'status-failed';
                const statusText = item.marks >= 35 ? 'Passed' : 'Failed';
                const maxMarks = Math.max(...students.map(s => s.marks));
                const isTopper = item.marks === maxMarks;

                row.innerHTML = `
                    <td>${item.id}</td>
                    <td>
                        ${item.name}
                        ${isTopper ? '<span class="topper-badge">TOPPER</span>' : ''}
                    </td>
                    <td>${item.age}</td>
                    <td>${item.course}</td>
                    <td>${item.marks}</td>
                    <td class="${statusClass}">${statusText}</td>
                `;
            }
            tableBody.appendChild(row);
        });
    }
    currentView = [...data];
    updateCartDisplay();
}

function setMode(mode) {
    currentMode = mode;
    document.body.setAttribute('data-mode', mode);

    // Switch active state of toggle buttons
    document.querySelectorAll('.mode-toggle button').forEach(btn => btn.classList.remove('active'));
    document.getElementById(`mode${mode.charAt(0).toUpperCase() + mode.slice(1)}`).classList.add('active');

    // Reset view based on mode
    if (mode === 'electronics') {
        renderTable(products);
    } else {
        renderTable(students);
    }

    // Reset search
    searchId.value = '';
    searchName.value = '';
}

function toggleCart(productId) {
    const product = products.find(p => p.id === productId);
    const cartIndex = cart.findIndex(item => item.id === productId);

    if (cartIndex > -1) {
        cart.splice(cartIndex, 1);
    } else {
        cart.push(product);
    }

    renderTable(currentView);
}

function updateCartDisplay() {
    const cartIcon = document.getElementById('cartCount');
    if (cartIcon) cartIcon.innerText = cart.length;

    const cartTotal = cart.reduce((sum, item) => sum + item.price, 0);
    const cartTotalEl = document.getElementById('cartTotal');
    if (cartTotalEl) cartTotalEl.innerText = `$${cartTotal.toLocaleString()}`;
}

function updateActiveButton(btnId) {
    document.querySelectorAll('.filter-buttons button').forEach(btn => btn.classList.remove('active'));
    if (btnId) document.getElementById(btnId).classList.add('active');
}

// --- Event Listeners ---

// Mode Toggles
document.getElementById('modeElectronics').addEventListener('click', () => setMode('electronics'));
document.getElementById('modeStudents').addEventListener('click', () => setMode('students'));

// Filter Functions
document.getElementById('showAll').addEventListener('click', () => {
    updateActiveButton('showAll');
    renderTable(currentMode === 'electronics' ? products : students);
});

// Category/Course Filters
document.getElementById('filterLaptops').addEventListener('click', () => {
    updateActiveButton('filterLaptops');
    renderTable(products.filter(p => p.category === "Laptops"));
});

document.getElementById('filterPhones').addEventListener('click', () => {
    updateActiveButton('filterPhones');
    renderTable(products.filter(p => p.category === "Smartphones"));
});

document.getElementById('filterAudio').addEventListener('click', () => {
    updateActiveButton('filterAudio');
    renderTable(products.filter(p => p.category === "Audio"));
});

document.getElementById('filterCS').addEventListener('click', () => {
    updateActiveButton('filterCS');
    renderTable(students.filter(s => s.course === "Computer Science"));
});

document.getElementById('filterBusiness').addEventListener('click', () => {
    updateActiveButton('filterBusiness');
    renderTable(students.filter(s => s.course === "Business"));
});

document.getElementById('filterArts').addEventListener('click', () => {
    updateActiveButton('filterArts');
    renderTable(students.filter(s => s.course === "Arts"));
});

// Rating/Marks Filters
document.getElementById('rating45').addEventListener('click', () => {
    updateActiveButton('rating45');
    renderTable(products.filter(p => p.rating >= 4.5));
});

document.getElementById('rating48').addEventListener('click', () => {
    updateActiveButton('rating48');
    renderTable(products.filter(p => p.rating >= 4.8));
});

document.getElementById('filterPass').addEventListener('click', () => {
    updateActiveButton('filterPass');
    renderTable(students.filter(s => s.marks >= 35));
});

document.getElementById('filterFail').addEventListener('click', () => {
    updateActiveButton('filterFail');
    renderTable(students.filter(s => s.marks < 35));
});

document.getElementById('filterDistinction').addEventListener('click', () => {
    updateActiveButton('filterDistinction');
    renderTable(students.filter(s => s.marks >= 75));
});

document.getElementById('showTopper').addEventListener('click', () => {
    updateActiveButton('showTopper');
    const maxMarks = Math.max(...students.map(s => s.marks));
    renderTable(students.filter(s => s.marks === maxMarks));
});

// Search Functions
function handleSearch() {
    const idVal = searchId.value.toLowerCase();
    const nameVal = searchName.value.toLowerCase();
    const sourceData = currentMode === 'electronics' ? products : students;

    const filtered = sourceData.filter(item =>
        item.id.toLowerCase().includes(idVal) &&
        item.name.toLowerCase().includes(nameVal)
    );

    updateActiveButton(null);
    renderTable(filtered);
}

searchId.addEventListener('input', handleSearch);
searchName.addEventListener('input', handleSearch);

// Sort Functions
document.getElementById('sortAsc').addEventListener('click', () => {
    const sortProp = currentMode === 'electronics' ? 'price' : 'marks';
    const sorted = [...currentView].sort((a, b) => a[sortProp] - b[sortProp]);
    renderTable(sorted);
});

document.getElementById('sortDesc').addEventListener('click', () => {
    const sortProp = currentMode === 'electronics' ? 'price' : 'marks';
    const sorted = [...currentView].sort((a, b) => b[sortProp] - a[sortProp]);
    renderTable(sorted);
});

// Transformation Functions
document.getElementById('viewCart').addEventListener('click', () => {
    renderTable(cart);
    updateActiveButton('viewCart');
});

document.getElementById('clearCart').addEventListener('click', () => {
    cart = [];
    renderTable(currentView);
    updateCartDisplay();
});
