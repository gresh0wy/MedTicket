;(function () {
	const form = document.querySelector('form')
	const fields = [
		{ id: 'name', errorId: 'name-error' },
		{ id: 'section', errorId: 'section-error' },
		{ id: 'category', errorId: 'category-error' },
		{ id: 'department', errorId: 'department-error' },
		{ id: 'contact_number', errorId: 'contact_number-error' },
		{ id: 'subject', errorId: 'subject-error' },
		{ id: 'description', errorId: 'description-error' },
		{ id: 'priority', errorId: null },
		{ id: 'recurrence', errorId: null },
	]

	const subject = document.getElementById('subject')
	const subjectCounter = document.getElementById('subject-counter')
	const subjectMax = 150

	const description = document.getElementById('description')
	const descriptionCounter = document.getElementById('description-counter')
	const descriptionMax = 500

	function updateCounter(el, counterEl, max) {
		counterEl.textContent = `${el.value.length} / ${max} znaków`
	}

	subject.addEventListener('input', () => updateCounter(subject, subjectCounter, subjectMax))
	description.addEventListener('input', () => updateCounter(description, descriptionCounter, descriptionMax))

	updateCounter(subject, subjectCounter, subjectMax)
	updateCounter(description, descriptionCounter, descriptionMax)

	form.addEventListener('submit', e => {
		let valid = true

		fields.forEach(({ id, errorId }) => {
			const input = document.getElementById(id)
			const error = errorId ? document.getElementById(errorId) : null
			const value = input.value.trim()

			const isEmpty = !value
			if (isEmpty) {
				input.classList.add('error')
				if (error) error.style.display = 'block'
				valid = false
			} else {
				input.classList.remove('error')
				if (error) error.style.display = 'none'
			}

			// Special case for maxlength enforcement
			if (id === 'subject' && value.length > subjectMax) {
				input.classList.add('error')
				document.getElementById('subject-error').style.display = 'block'
				valid = false
			}

			if (id === 'description' && value.length > descriptionMax) {
				input.classList.add('error')
				document.getElementById('description-error').style.display = 'block'
				valid = false
			}
		})

		if (!valid) {
			e.preventDefault()
			const firstError = document.querySelector('.error')
			if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' })
		}
	})
})()(function () {
	const input = document.getElementById('subject')
	const counter = document.getElementById('subject-counter')
	const errorBox = document.getElementById('subject-error')
	const MAX = 150

	// Aktualizuj licznik i waliduj na każdą zmianę
	input.addEventListener('input', () => {
		const len = input.value.length
		counter.textContent = `${len} / ${MAX} znaków`

		if (len > MAX) {
			// w praktyce z maxlength nie da się tutaj wejść, ale zachowujemy logikę
			errorBox.style.display = 'block'
		} else {
			errorBox.style.display = 'none'
		}
	})

	// Dodatkowo – przy próbie wysłania formularza
	input.form.addEventListener('submit', e => {
		if (input.value.length > MAX) {
			e.preventDefault()
			errorBox.style.display = 'block'
			input.focus()
		}
	})
})()
