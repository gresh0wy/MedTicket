# MedTicket

## Opis projektu

MedTicket to aplikacja PHP do zarządzania zgłoszeniami medycznymi (lub innymi zgłoszeniami wymagającymi bezpiecznego przetwarzania). Projekt wykorzystuje Composer do zarządzania zależnościami, plik `.env` do przechowywania konfiguracji środowiskowej oraz SSL (HTTPS) do zabezpieczenia komunikacji.

---

## Wymagania

- **PHP** w wersji co najmniej **7.4** (zalecane **8.x**)
- **Composer** (https://getcomposer.org/)
- Serwer WWW z obsługą PHP (Apache, Nginx, XAMPP itp.)
- **Rozszerzenia PHP**:  
  - `ext-mbstring`  
  - `ext-openssl`  
  - `ext-pdo_mysql` (lub `ext-pdo_pgsql`, jeśli używasz PostgreSQL)  
- Plik konfiguracyjny **`.env`** w katalogu głównym projektu  
- **Certyfikat SSL** (HTTPS) — zalecany w środowisku produkcyjnym  

---


## Opis katalogów

- **public/** – zawiera pliki dostępne publicznie, np. punkt wejścia `index.php`.
- **src/** – główny kod źródłowy aplikacji (kontrolery, modele, logika biznesowa).
- **vendor/** – biblioteki i zależności zainstalowane przez Composera.
- **.env** – plik konfiguracyjny (zazwyczaj zawiera dane dostępowe do bazy, ustawienia środowiska).
- **composer.json** – plik definiujący zależności PHP projektu.
- **composer.lock** – plik blokujący wersje zainstalowanych pakietów.
- **README.md** – dokumentacja projektu, którą właśnie czytasz.


---

## Instalacja

1. **Sklonuj repozytorium**:

   ```bash
   git clone https://github.com/gresh0wy/MedTicket.git
   cd medticket

2. **Zainstaluj zależności:**
composer install

W pliku composer.json masz zdefiniowaną bibliotekę:
{
  "require": {
    "vlucas/phpdotenv": "^5.6"
  }
}

Pakiet vlucas/phpdotenv pozwala wczytywać zmienne środowiskowe z pliku .env.

3. **Przygotuj plik .env:**

Skopiuj plik przykładowy i zmień nazwę na .env:
cp .env.example .env
