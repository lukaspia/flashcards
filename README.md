# Flashcards

**Flashcards** to intuicyjna aplikacja zaprojektowana do **nauki języków** i przeglądania słownictwa za pomocą cyfrowych fiszek. Stworzyłam ją, aby ułatwić efektywne przyswajanie nowych słów i zwrotów.

![GitHub Release](https://img.shields.io/github/v/release/lukaspia/flashcards)
![GitHub License](https://img.shields.io/github/license/lukaspia/flashcards)

---

### Kluczowe Funkcje

* **Własne zestawy fiszek:** Twórz własne fiszki z pytaniami i odpowiedziami, pogrupowane w **lekcje**, aby spersonalizować materiały do nauki języków.
* **Tłumaczenia i przykłady wspierane przez AI:** Dodane słowa są automatycznie tłumaczone przez AI, a także generowane jest przykładowe zdanie demonstrujące ich użycie.
* **Text-to-Speech:** Słuchaj słów czytanych na głos przez wbudowanego lektora, co poprawia wymowę i zapamiętywanie.
* **Elastyczne tryby nauki:** Uporządkuj słowa sekwencyjnie lub losowo, aby zwiększyć efektywność nauki.
* **Intuicyjny interfejs:** Zaprojektowany z myślą o prostocie i łatwości użytkowania, skupiający się na doświadczeniu nauki języków.

---

### Wykorzystane Technologie

Aplikacja została zbudowana z wykorzystaniem następujących technologii:

* **Frontend:** React
* **Backend:** Symfony, PHP
* **Baza danych:** MySQL
* **Inne:** JavaScript, TypeScript

---

### Lokalna konfiguracja

Aby uruchomić aplikację lokalnie, wykonaj następujące kroki:

1.  Sklonuj repozytorium:
    ```bash
    git clone [https://github.com/lukaspia/flashcards.git](https://github.com/lukaspia/flashcards.git)
    ```
2.  Przejdź do katalogu projektu:
    ```bash
    cd flashcards
    ```
3.  Zainstaluj zależności backendu (PHP/Symfony):
    ```bash
    composer install
    ```
4.  Skonfiguruj zmienne środowiskowe. Skopiuj plik `.env.dist` do nowego pliku o nazwie `.env`:
    ```bash
    cp .env.dist .env
    ```
    *Możesz również utworzyć plik `.env.local`, aby nadpisać zmienne dla swojego środowiska lokalnego. Plik ten ma wyższy priorytet niż `.env` i jest ignorowany przez Git.*
5.  Otwórz nowo utworzony plik `.env` (lub `.env.local`) i uzupełnij wymagane wartości:
    *   `DATABASE_URL`: Pełny URL do Twojej bazy danych.
    *   `APP_SECRET`: Unikalny sekret dla Twojej aplikacji Symfony.
    *   `GEMINI_API_KEY`: Twój klucz API do usług Google Gemini.
6.  Uruchom migracje bazy danych:
    ```bash
    php bin/console doctrine:migrations:migrate
    ```
7.  Zainstaluj zależności frontendowe (Node.js/React):
    ```bash
    npm install
    # lub
    yarn install
    ```
8.  Zbuduj frontend:
    ```bash
    npm run build
    # lub
    yarn build
    ```

---

### Komendy konsoli

Aplikacja udostępnia kilka komend konsoli do zadań administracyjnych:

* `app:add-user`: Dodaj nowego użytkownika do systemu.
* `app:add-word-category`: Twórz nowe kategorie dla słów.
* `app:cleanup-temp`: Wyczyść tymczasowe obrazy (może być użyte jako opcja zadania cron do automatyzacji).
* `app:delete-user`: Usuń istniejącego użytkownika.
* `app:delete-word-category`: Usuń kategorię słów.
* `app:list-users`: Wyświetl listę wszystkich zarejestrowanych użytkowników.

---

### Przyszły rozwój

Planuję zaimplementować następujące funkcje w przyszłych wersjach:

* Wsparcie dla większej liczby języków.
* Rejestracja użytkowników za pomocą formularza.
* Tryb ciemny dla lepszego doświadczenia użytkownika.
* Ogólne ulepszenia UI/UX.
* Opcja zmiany głosu lektora.
* Automatyczny wybór obrazu podglądu dla każdego słowa.
* Możliwość dodawania kategorii słów za pomocą formularza bezpośrednio w aplikacji.

---

### Wsparcie i wkład

Jeśli masz jakieś pytania, sugestie lub napotkasz problemy, śmiało otwórz **Issue** w tym repozytorium.