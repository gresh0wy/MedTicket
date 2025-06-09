document.addEventListener('DOMContentLoaded', function () {
	const sectionSelect = document.getElementById('section')
	const categorySelect = document.getElementById('category')
	const departmentSearchInput = document.getElementById('department-search')
	const departmentList = document.getElementById('department-list')
	const departmentInput = document.getElementById('department')

	const categories = {
		informatyczna: ['Serwis', 'Drukarki', 'AMMS', 'Infomedica', 'Aktulizacja', 'Przenosiny', 'Ris/Pacs'],
		elektryczna: ['Instalacja', 'Oświetlenie', 'Zasilanie awaryjne'],
		cyber: ['Atak hakerski', 'Phishing', 'Zabezpieczenia'],
		budowlana: ['Remont', 'Budowa', 'Instalacje wodne'],
		aparatura: ['Serwis', 'Kalibracja', 'Naprawa'],
	}

	const departments = {
		'Dyrekcja Szpitala': [
			'Sekretariat',
			'Sekretariat Dyrektora Naczelnego',
			'Z-ca Dyr. ds. Lecznictwa',
			'Z-ca Dyr. ds. Technicznych',
			'Z-ca Dyr. ds Finansowych',
			'Naczelna Pielęgniarka',
		],
		'Dział Epidemiologii': ['Kierownik Działu Epidemiologii'],
		'ODDZIAŁY SZPITALNE': {
			'Oddział Chirurgii Ortopedyczno – Urazowej': [
				'Lekarz Kierujący Oddziałem',
				'Pielęgniarka Oddziałowa',
				'Sekretariat',
				'Dyżurka Lekarska',
				'Biblioteka',
				'Dyżurka Lekarska nocna odc. Kobiecy',
				'Dyżurka Pielęgniarek odc. Kobiecy',
				'Dyżurka Pielęgniarek odc. Męski',
				'Telefon dla pacjenta odc. Kobiecy',
				'Telefon dla pacjenta odc. Męski',
			],
			'Oddział Urologiczny': [
				'Lekarz Kierujący Oddziałem',
				'Pielęgniarka Oddziałowa',
				'Sekretariat',
				'Dyżurka Lekarska',
				'Dyżurka Pielęgniarek',
				'Pokój socjalny',
				'Pokój zabiegowy',
				'Izba przyjęć',
				'Telefon dla pacjenta',
			],
			'Oddział Chirurgii Dzieci': [
				'Lekarz Kierujący Oddziałem',
				'Pielęgniarka Oddziałowa',
				'Sekretariat',
				'Dyżurka Lekarska',
				'Dyżurka Pielęgniarek',
				'Pokój zabiegowy',
				'Pokój badań',
				'Dyżurka nocna Lekarska',
				'Telefon dla pacjenta',
			],
			'Oddział Pediatryczny': [
				'Lekarz Kierujący Oddziałem',
				'Pielęgniarka Oddziałowa',
				'Dyżurka Lekarska',
				'Dyżurka Pielęgniarek odc. Dzieci starszych',
				'Dyżurka Pielęgniarek odc. Niemowlęcy',
				'Pokój badań',
				'Izba przyjęć',
				'Poradnia pediatryczna',
				'Pracownia Pulmunologii i Alergologii',
				'Pracownia Kardiologiczna',
				'Pracownia Echo i USG',
				'Rezydenci',
				'Sala odpraw',
				'Telefon dla pacjenta',
			],
			'Oddział Anestezjologii i Intensywnej Terapii': [
				'Lekarz Kierujący Oddziałem',
				'Pielęgniarka Oddziałowa',
				'Sekretariat',
				'Dyżurka Lekarska Anestezjologów',
				'Pokój narad',
				'Dyżurka Pielęgniarek',
				'Pokój socjalny Pielęgniarek',
				'Pokój socjalny Lekarzy',
				'Sala chorych nr 1',
				'Sala chorych nr 2',
				'Sala chorych nr 3',
				'Sala chorych nr 4',
				'Sala chorych nr 5',
				'Sala chorych nr 6',
				'Sala chorych nr 7',
				'Sala chorych nr 8',
			],
			'Oddział Chorób Wewnętrznych': ['Oddział Chorób Wewnętrznych piętro I', 'Oddział Chorób Wewnętrznych piętro II'],
			'Oddział Chorób Wewnętrznych piętro I': [
				'Lekarz Kierujący Oddziałem',
				'Pielęgniarka Oddziałowa',
				'Dyżurka Lekarska',
				'Dyżurka Pielęgniarek',
				'Poradnia Konsultacyjna Cukrzycowa',
				'Telefon dla pacjenta',
				'Winda',
			],
			'Oddział Chorób Wewnętrznych piętro II': [
				'Sekretariat',
				'Dyżurka Lekarska',
				'Dyżurka Pielęgniarek',
				'Intensywny Nadzór Kardiologiczny',
			],
			'Oddział Chorób Wewnętrznych i Kardiologii z Odcinkiem Intensywnej Terapii Kardiologicznej': [
				'Lekarz Kierujący Oddziałem',
				'Pielęgniarka Oddziałowa',
				'Sekretariat',
				'Z-ca Kier. Oddziału',
				'Dyżurka Lekarska',
				'Dyżurka Pielęgniarek',
				'Intensywny Nadzór Kardiologiczny',
				'Pracownia prób wysiłkowych, Holtera i EEG',
			],
			'Oddział Dermatologiczny z Pododdziałem': [
				'Lekarz Kierujący Oddziałem',
				'Pielęgniarka Oddziałowa',
				'Sekretariat',
				'Dyżurka Lekarska',
				'Dyżurka Lekarska pok. 15',
				'Dyżurka Pielęgniarek',
				'Dyżurka Pielęgniarek odc. Dzieci',
				'Gabinet Fototerapii PUVA',
				'Pokój badań nr 5',
				'Telefon dla pacjenta odc. dzieci',
				'Telefon dla pacjenta odc. dorośli',
			],
			'Oddział Okulistyczny z Pododdziałem': [
				'Lekarz Kierujący Oddziałem',
				'Pielęgniarka Oddziałowa',
				'Sekretariat',
				'Dyżurka Lekarska',
				'Dyżurka Pielęgniarek',
				'Sala operacyjna',
				'USG',
				'Pokój socjalny',
				'Telefon dla pacjenta',
			],
			'Oddział Otolaryngologiczny z Pododdziałem': [
				'Lekarz Kierujący Oddziałem',
				'Pielęgniarka Oddziałowa',
				'Sekretariat',
				'Dyżurka Lekarska',
				'Punkt przyjęć',
				'Dyżurka Pielęgniarek',
				'Sala operacyjna',
				'Audiometria',
				'Poradnia Otolaryngologiczna Rejestracja',
				'Pokój socjalny',
				'Punkt przyjęć na Oddział',
				'Telefon dla pacjenta',
			],
			'Oddział Obserwacyjno-Zakaźny': [
				'Lekarz Kierujący Oddziałem',
				'Pielęgniarka Oddziałowa',
				'Sekretariat odc. Dorosłych',
				'Dyżurka Lekarska odc. Dorosłych',
				'Dyżurka Pielęgniarek odc. Dorosłych',
				'Dyżurka Lekarska',
				'Dyżurka Pielęgniarek',
			],
			'Oddział Chorób Infekcyjnych i Pediatrii': [
				'Lekarz Kierujący Oddziałem',
				'Sekretariat',
				'Pielęgniarka Oddziałowa',
				'Dyżurka Lekarska',
				'Dyżurka Pielęgniarek',
				'Izba przyjęć',
			],
			'Oddział Chirurgii Ogólnej, Onkologicznej i Małoinwazyjnej': [
				'Lekarz Kierujący Oddziałem',
				'Pielęgniarka Oddziałowa',
				'Sekretariat',
				'Dyżurka Lekarska',
				'Dyżurka Piel. Odc. Męski',
				'Dyżurka Piel .odc. kobiecy',
				'Sala pooperacyjna odc. Kobiecy',
				'Biblioteka',
				'Pokój badań',
				'Telefon dla pacjenta odc. Kobiecy',
				'Telefon dla pacjenta odc. Męski',
			],
			'Oddział Neurologiczny z Poddziałem Leczenia Udarów Mózgu': [
				'Lekarz Kierujący Oddziałem',
				'Pielęgniarka Oddziałowa',
				'Sekretariat',
				'Dyżurka Lekarska',
				'Dyżurka Pielęgniarek',
				'Pracownia EEG',
				'Sala Udarowa',
			],
			'Oddział Ginekologiczny-Położniczy z salą Porodową': [
				'Lekarz Kierujący Oddziałem',
				'Pielęgniarka Oddziałowa',
				'Sekretariat',
				'Dyżurka Lekarska',
				'Dyżurka Lekarska nocna',
				'Dyżurka Pielęgniarek odc. Ginekologia parter',
				'Dyżurka Pielęgniarek odc. Ginekologia I Piętro',
				'Izba przyjęć Ginekologiczna',
				'Izba przyjęć położnicza',
				'Trakt porodowy',
				'Poradnia laktacyjna',
				'Sala porodowa septyczna',
				'Dyżurka Pielęgniarek odc. Położniczy parter',
				'Dyżurka Pielęgniarek odc. Położniczy I piętro',
				'Szkoła rodzenia',
				'Ginekologia parter',
				'Sala operacyjna',
				'Telefon dla pacjenta Ginekologia parter',
				'Telefon dla pacjenta - Patologia ciąży',
				'Telefon dla pacjenta - Położnictwo A',
				'Telefon dla pacjenta - Położnictwo B',
			],
			'Oddział Noworodków': [
				'Lekarz Kierujący Oddziałem',
				'Pielęgniarka Oddziałowa',
				'Sekretariat',
				'Dyżurka Lekarska',
				'Patologia noworodka',
				'Izolatka',
				'Fizjologia',
				'Intensywny Nadzór',
				'Poradnia',
			],
			'Szpitalny Oddział Ratunkowy': [
				'Kierownik Szpitalnego Oddziału Ratunkowego',
				'Pielęgniarka Oddziałowa',
				'Sekretariat',
				'Rejestracja',
				'Dyżurka Lekarska Chirurgiczna nocna',
				'Dyżurka Lekarska Internistyczna nocna',
				'Gabinet Lekarski Chirurgiczny',
				'Gabinet Lekarski Internistyczny',
				'Sala Obserwacyjna',
				'Sala Intensywnej Terapii',
				'Pokój Konsultacyjny',
				'Pokój Dekontaminacyjny',
				'Sala Resuscytacyjno - zabiegowa',
				'Pokój opatrunków gipsowych',
				'Telefon dla pacjenta',
			],
		},
		DZIAŁY: {
			'Dział Diagnostyki Obrazowej': [
				'Kierownik Poradni Diagnostyki Obrazowej',
				'Pielęgniarka Oddziałowa',
				'Dyżurka Lekarska',
				'Fizyk medyczny',
				'Tomograf',
				'Tomograf sterownia',
				'Rejestracja',
				'Sterownia nr 1 i nr 2',
				'Pracownia RTG nr 1 i nr 2',
				'Pracownia ECHO SERCA',
				'Pracownia USG',
				'Pokój techników',
			],
			'Pracownia Endoskopowa': ['Rejestracja Badań Przesiewowych raka jelita grubego', 'FAX- Endoskopia'],
			'Dział Rehabilitacji': ['Rehabilitacja'],
			'Dział Epidemiologii': ['Kierownik Działu Epidemiologii'],
			'Dział Nocnej i Świątecznej Opieki Medycznej': ['Rejestracja przychodni całodobowej', 'Poradnia całodobowa'],
			'Dział Żywienia': ['Stanowisko ds. Żywienia'],
		},
		PORADNIE: [
			'Koordynator działalności ambulatoryjnej szpitala',
			'Rejestracja ogólna',
			'Poradnia Chirurgii Ortopedyczno-Urazowej',
			'Poradnia Nefrologiczna Dzieci',
			'Poradnia Chirurgii Dzieci',
			'Poradnia Urologiczna',
			'Poradnia Dermatologiczna REJESTRACJA',
			'Poradnia W',
			'Gabinet testów alergologicznych',
			'Wideodermatoskop',
			'Pokój zabiegowy',
			'Poradnia Dermatologii Alergologicznej',
			'Poradnia Dermatologiczna Dzieci',
			'Poradnia Dermatologiczna Dorosłych',
			'Poradnia Okulistyczna',
			'Poradnia Okulistyczna Dzieci',
			'Poradnia Okulistyczna Pielęgniarka',
			'Poradnia okulistyczna ortoptysta',
			'Poradnia Leczenia Zeza -dzieci',
			'Poradnia Otolaryngologiczna',
			'Poradnia Chorób Zakaźnych Dorosłych',
			'Poradnia Chorób Zakaźnych Dzieci',
			'Poradnia Chirurgii Ogólnej',
			'Poradnia Neurologiczna',
			'Poradnia Proktologiczna',
			'Poradnia Ginekologiczna i Patologii Ciąży',
			'Poradnia Laktacyjna',
			'Poradnia Neonatologiczna',
			'Poradnia Psychologii Klinicznej',
			'Poradnia Medycyny Pracy',
			'Poradnia anestezjologiczna',
		],
		POZOSTAŁE: [
			'Apteka Szpitalna',
			'Kierownik Apteki',
			'Magazyn płynów infuzyjnych',
			'Receptura',
			'Magazyn materiałów niesterylnych',
			'Sterylny sprzęt jednorazowy',
			'Specyfiki-leki gotowe',
			'Księgowość',
			'Przyjmowanie leków',
			'Pracownia żywienia pozajelitowego',
			'Bank Krwi',
		],
		'Blok Operacyjny': [
			'Kierownik Traktu Operacyjno - Chirurgicznego',
			'Pielęgniarka Oddziałowa',
			'Dyżurka Instrumentariuszek',
			'Pokój przygotowania pacjenta nr 1',
			'Pokój przygotowania pacjenta nr 2',
			'Pokój przygotowania pacjenta nr 3',
			'Pokój przygotowania pacjenta nr 4',
			'Pokój przygotowania pacjenta nr 5',
			'Pokój przygotowania pacjenta nr 6',
			'Sala operacyjna nr 1',
			'Sala operacyjna nr 2',
			'Sala operacyjna nr 3',
			'Sala operacyjna nr 4',
			'Sala operacyjna nr 5',
			'Sala operacyjna nr 6',
			'Sala wybudzeniowa',
			'Blok Operacyjny lekarze',
			'Pokój socjalny',
		],
		'Szkoła Rodzenia': ['Szkoła rodzenia'],
		'Centralna Sterylizatornia': ['Kierownik', 'Pokój socjalny', 'Strona brudna', 'Strona czysta', 'Strona jałowa'],
		'Pielęgniarka Społeczna': ['Pielęgniarka Społeczna'],
		'Koordynator Pakietu Onkologicznego': ['Koordynator Pakietu Onkologicznego'],
		'Poradnia Pracownicza': ['Lekarz Zakładowy'],
		ADMINISTRACJA: [
			'Kancelaria Szpitala',
			'Kancelaria Szpitala',
			'Sekcja Informatyczna',
			'Informatycy',
			'Warsztat naprawczy sprzętu komputerowego',
			'Zespół Radców Prawnych',
			'Radcy Prawni',
		],
		'SAMODZIELNE STANOWISKA PRACY': [
			'Pełnomocnik ds. akredytacji',
			'Pełnomocnik ds. zarządzania jakością',
			'Pełnomocnik ds. uchodźców z Ukrainy',
			'Pełnomocnik ds. Praw Pacjenta',
			'Stanowisko ds. P/POŻ.',
			'Stanowisko ds. BHP',
			'Stanowisko ds OC',
			'Kapelan',
		],
		'Dział Kadr, Płac i Organizacji': ['Kierownik Działu Kadr Płac i Organizacji'],
		'Sekcja Kadr': ['Sekcja Kadr'],
		'Sekcja Płac': ['Sekcja Płac'],
		'Dział Organizacyjny': [
			'Kancelaria Dyrektora',
			'Sekcja ds. Organizacji',
			'Praktyki studenckie',
			'Wydawanie dokumentacji medycznej',
			'Składnica akt',
			'Pokój kontrolera zewnętrznego',
		],
		'Sekcja Zamówień Publicznych': ['Kierownik Sekcji', 'Sekcja Zamówień Publicznych'],
		'Dział Księgowości': ['Główny Księgowy', 'Z-ca Głównego księgowego', 'Dział Księgowości Finansowej', 'Kasa'],
		'Dział Księgowości Materiałowej i Inwentaryzacyjnej': ['Dział Księgowości Materiałowej i Inwentaryzacyjnej'],
		'Dział Controllingu': ['Dział Controllingu'],
		'Dział Rozliczeń, Statystyki': ['Kierownik Działu', 'Dział Rozliczeń, Statystyki i Dok. Medycznej'],
		'Dział Logistyki': ['Kierownik Działu Logistyki', 'Dział Logistyki'],
		'Sekcja Zaopatrzeniowo – Gospodarcza': [
			'Kierownik',
			'Magazyn gospodarczy',
			'Magazyn ubraniowy',
			'Sala Konferencyjna',
		],
		'Sekcja Aparatury Medycznej I Sprzętu Medycznego': [
			'Starszy specjalista ds sprzętu medycznego',
			'Specjalista ds sprzętu medycznego',
		],
		'Dział Techniczny': [
			'Sekcja Ogólnobudowlana I Utrzymania Ruchu',
			'Kierownik Sekcji Ogólnobudowlanej',
			'Warsztat stolarski',
			'Tleniarz',
			'Dyspozytor Stacji Uzdatniania Wody',
			'Sekcja ogólnobudowlana',
		],
		'Sekcja Elektryczna': ['Kierownik Sekcji Elektrycznej', 'Dyżurny elektryk', 'Teletechnik'],
	}
	document.querySelector('form').addEventListener('submit', function (event) {
		let isValid = true

		// Pobieranie elementów formularza
		const nameInput = document.getElementById('name')
		const sectionInput = document.getElementById('section')
		const categoryInput = document.getElementById('category')
		const departmentInput = document.getElementById('department')
		const departmentSearchInput = document.getElementById('department-search')
		const contactNumberInput = document.getElementById('contact_number')
		const descriptionInput = document.getElementById('description')

		// Pobieranie elementów do wyświetlania błędów
		const nameError = document.getElementById('name-error')
		const sectionError = document.getElementById('section-error')
		const categoryError = document.getElementById('category-error')
		const departmentError = document.getElementById('department-error')
		const contactNumberError = document.getElementById('contact_number-error')
		const descriptionError = document.getElementById('description-error')

		// Walidacja pola "Imię i Nazwisko"
		if (!nameInput.value) {
			nameInput.classList.add('input-error')
			nameError.style.display = 'block'
			isValid = false
		} else {
			nameInput.classList.remove('input-error')
			nameError.style.display = 'none'
		}

		// Walidacja pola "Sekcja"
		if (!sectionInput.value) {
			sectionInput.classList.add('input-error')
			sectionError.style.display = 'block'
			isValid = false
		} else {
			sectionInput.classList.remove('input-error')
			sectionError.style.display = 'none'
		}

		// Walidacja pola "Kategoria"
		if (!categoryInput.value) {
			categoryInput.classList.add('input-error')
			categoryError.style.display = 'block'
			isValid = false
		} else {
			categoryInput.classList.remove('input-error')
			categoryError.style.display = 'none'
		}

		// Walidacja pola "Nazwa oddziału"
		if (!departmentInput.value) {
			departmentSearchInput.classList.add('input-error')
			departmentError.style.display = 'block'
			isValid = false
		} else {
			departmentSearchInput.classList.remove('input-error')
			departmentError.style.display = 'none'
		}

		// Walidacja pola "Numer wewnętrzny"
		if (!contactNumberInput.value) {
			contactNumberInput.classList.add('input-error')
			contactNumberError.style.display = 'block'
			isValid = false
		} else {
			contactNumberInput.classList.remove('input-error')
			contactNumberError.style.display = 'none'
		}

		// Walidacja pola "Opis problemu"
		if (!descriptionInput.value) {
			descriptionInput.classList.add('input-error')
			descriptionError.style.display = 'block'
			isValid = false
		} else {
			descriptionInput.classList.remove('input-error')
			descriptionError.style.display = 'none'
		}

		// Zatrzymanie wysyłki formularza, jeśli którykolwiek z warunków nie jest spełniony
		if (!isValid) {
			event.preventDefault()
		}
	})

	sectionSelect.addEventListener('change', function () {
		const selectedSection = this.value
		categorySelect.innerHTML = "<option value=''>Wybierz</option>"
		if (selectedSection && categories[selectedSection]) {
			categories[selectedSection].forEach(function (category) {
				const option = document.createElement('option')
				option.value = category.toLowerCase()
				option.textContent = category
				categorySelect.appendChild(option)
			})
		}
	})

	function showAllDepartments() {
		departmentList.innerHTML = ''
		for (const [mainDept, subDepts] of Object.entries(departments)) {
			if (Array.isArray(subDepts)) {
				subDepts.forEach(subDept => {
					const option = document.createElement('div')
					option.textContent = `${mainDept} - ${subDept}`
					option.addEventListener('click', () => {
						departmentSearchInput.value = `${mainDept} - ${subDept}`
						departmentInput.value = `${mainDept} - ${subDept}`
						departmentList.style.display = 'none'
					})
					departmentList.appendChild(option)
				})
			} else {
				for (const [subMainDept, subSubDepts] of Object.entries(subDepts)) {
					subSubDepts.forEach(subSubDept => {
						const option = document.createElement('div')
						option.textContent = `${subMainDept} - ${subSubDept}`
						option.addEventListener('click', () => {
							departmentSearchInput.value = `${subMainDept} - ${subSubDept}`
							departmentInput.value = `${subMainDept} - ${subSubDept}`
							departmentList.style.display = 'none'
						})
						departmentList.appendChild(option)
					})
				}
			}
		}
		departmentList.style.display = 'block'
	}

	function filterDepartments() {
		const searchTerm = departmentSearchInput.value.toLowerCase()
		departmentList.innerHTML = ''
		for (const [mainDept, subDepts] of Object.entries(departments)) {
			if (Array.isArray(subDepts)) {
				subDepts.forEach(subDept => {
					const fullDept = `${mainDept} - ${subDept}`.toLowerCase()
					if (fullDept.includes(searchTerm)) {
						const option = document.createElement('div')
						option.textContent = `${mainDept} - ${subDept}`
						option.addEventListener('click', () => {
							departmentSearchInput.value = `${mainDept} - ${subDept}`
							departmentInput.value = `${mainDept} - ${subDept}`
							departmentList.style.display = 'none'
						})
						departmentList.appendChild(option)
					}
				})
			} else {
				for (const [subMainDept, subSubDepts] of Object.entries(subDepts)) {
					subSubDepts.forEach(subSubDept => {
						const fullDept = `${subMainDept} - ${subSubDept}`.toLowerCase()
						if (fullDept.includes(searchTerm)) {
							const option = document.createElement('div')
							option.textContent = `${subMainDept} - ${subSubDept}`
							option.addEventListener('click', () => {
								departmentSearchInput.value = `${subMainDept} - ${subSubDept}`
								departmentInput.value = `${subMainDept} - ${subSubDept}`
								departmentList.style.display = 'none'
							})
							departmentList.appendChild(option)
						}
					})
				}
			}
		}
		if (departmentList.innerHTML === '') {
			departmentList.style.display = 'none'
		} else {
			departmentList.style.display = 'block'
		}
	}

	departmentSearchInput.addEventListener('input', filterDepartments)
	departmentSearchInput.addEventListener('focus', showAllDepartments)
	departmentSearchInput.addEventListener('click', function (event) {
		event.stopPropagation()
		showAllDepartments()
	})
	departmentList.addEventListener('click', function (event) {
		event.stopPropagation()
	})
	document.addEventListener('click', event => {
		if (!event.target.closest('.dropdown')) {
			departmentList.style.display = 'none'
		}
	})

	// Validation for name field allowing Polish characters
	const nameInput = document.getElementById('name')
	nameInput.addEventListener('input', function () {
		this.value = this.value.replace(/[^a-zA-Z\sąćęłńóśźżĄĆĘŁŃÓŚŹŻ]/g, '')
	})

	// Validation for contact number field
	const contactNumberInput = document.getElementById('contact_number')
	contactNumberInput.addEventListener('input', function () {
		this.value = this.value.replace(/[^0-9]/g, '')
	})
})




