document.addEventListener('DOMContentLoaded', function () {
	const toggleDropdown = (event, dropdown) => {
		// Zamknij inne dropdowny
		const openDropdowns = document.querySelectorAll('.dropdown-content')
		openDropdowns.forEach(d => {
			if (d !== dropdown) {
				d.style.display = 'none' // Zamknij inne otwarte dropdowny
			}
		})
		// Przełącz widoczność klikniętego dropdowna
		dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block'
	}

	// Zabezpieczenie przed brakiem elementów w przypadku braku odpowiednich uprawnień
	const userName = document.querySelector('.user-name')
	const userDropdown = document.querySelector('.user-menu .dropdown-content')
	if (userName) {
		userName.addEventListener('click', event => {
			event.stopPropagation() // Zatrzymaj propagację kliknięcia
			toggleDropdown(event, userDropdown) // Przełącz dropdown użytkownika
		})
	}

	const adminLink = document.querySelector('.admin-link')
	const adminDropdown = document.querySelector('.admin-dropdown')
	if (adminLink) {
		adminLink.addEventListener('click', event => {
			event.preventDefault() // Zablokuj domyślne działanie linku
			event.stopPropagation() // Zatrzymaj propagację kliknięcia
			toggleDropdown(event, adminDropdown) // Przełącz dropdown administracji
		})
	}

	const ordersLink = document.querySelector('.orders-link')
	const ordersDropdown = document.querySelector('.orders-dropdown')
	if (ordersLink) {
		ordersLink.addEventListener('click', event => {
			event.preventDefault() // Zablokuj domyślne działanie linku
			event.stopPropagation() // Zatrzymaj propagację kliknięcia
			toggleDropdown(event, ordersDropdown) // Przełącz dropdown sekcji
		})
	}

	// Zamykanie dropdownów po kliknięciu poza nimi
	window.addEventListener('click', () => {
		const openDropdowns = document.querySelectorAll('.dropdown-content')
		openDropdowns.forEach(d => {
			d.style.display = 'none' // Zamknij każdy dropdown
		})
	})
})
