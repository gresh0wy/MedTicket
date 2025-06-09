document.addEventListener("DOMContentLoaded", function() {
    const sidebar = document.querySelector('.sidebar');
    const toggleButton = document.querySelector('.sidebar-toggle');

    toggleButton.addEventListener('click', () => {
        sidebar.classList.toggle('expanded');
    });
});

function toggleDropdown() {
    document.getElementById("dropdown-content").classList.toggle("show");
}
