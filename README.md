# Flashcards

**Flashcards** is an intuitive application designed for **language learning** and reviewing vocabulary using digital flashcards. I created it to facilitate the efficient acquisition of new words and phrases.

---

### Key Features

* **Custom Flashcard Sets:** Create your own question-and-answer flashcards, grouped into **lessons**, to personalize your language learning material.
* **AI-Powered Translations and Examples:** Added words are automatically translated by AI, and an example sentence demonstrating their usage is generated.
* **Text-to-Speech:** Listen to words read aloud by a built-in lecturer for improved pronunciation and retention.
* **Flexible Learning Modes:** Arrange words in sequence or randomly to enhance learning efficiency.
* **Intuitive Interface:** Designed for simplicity and ease of use, focusing on the language learning experience.

---

### Technologies Used

The application is built with the following technologies:

* **Frontend:** React
* **Backend:** Symfony, PHP
* **Database:** MySQL
* **Other:** JavaScript, TypeScript

---

### Local Setup

To run the application locally, follow these steps:

1.  Clone the repository:
    ```bash
    git clone [https://github.com/lukaspia/flashcards.git](https://github.com/lukaspia/flashcards.git)
    ```
2.  Navigate to the project directory:
    ```bash
    cd flashcards
    ```
3.  Install backend dependencies (PHP/Symfony):
    ```bash
    composer install
    ```
4.  Configure your database connection in the `.env` (or `.env.local`) file.
5.  Run database migrations:
    ```bash
    php bin/console doctrine:migrations:migrate
    ```
6.  Install frontend dependencies (Node.js/React):
    ```bash
    npm install
    # or
    yarn install
    ```
7.  Build the frontend:
    ```bash
    npm run build
    # or
    yarn build
    ```

---

### Console Commands

The application provides several console commands for administrative tasks:

* `app:add-user`: Add a new user to the system.
* `app:add-word-category`: Create new categories for words.
* `app:cleanup-temp`: Clean up temporary images (can be used as a cron job option for automation).
* `app:delete-user`: Remove an existing user.
* `app:delete-word-category`: Delete a word category.
* `app:list-users`: List all registered users.

---

### Future Development

I plan to implement the following features in future versions:

* Support for more languages.
* User registration via a form.
* Dark mode for improved user experience.
* Overall UI/UX enhancements.
* Option to change the lecturer's voice.
* Automatic selection of a preview image for each word.
* Ability to add word categories via a form directly in the application.

---

### Live Application

*(Leave this section for the link to your live application, e.g., "You can try the live application here: [Link to your live app]")*

---

### Support and Contribution

If you have any questions, suggestions, or encounter issues, feel free to open an **Issue** in this repository.