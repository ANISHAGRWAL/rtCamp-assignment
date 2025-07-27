Of course\! Here is a draft for your GitHub README.md file. It's designed to be clear, concise, and professional, explaining your project's functionality while highlighting your skills.

You can copy and paste the content below directly into a `README.md` file in your repository.

-----

# XKCD Comic Email Subscription System

This project is a PHP-based web application that allows users to subscribe to a mailing list to receive a random XKCD comic in their inbox every day. It features a secure email verification system for both subscribing and unsubscribing, and it uses a CRON job for automated daily emails.

This project was developed as a part of the placement assignment for **rtCamp**.

-----

## 📸 Screenshots

Here’s a quick look at the application's interface.

## Subscription Page
<img width="466" height="609" alt="image" src="https://github.com/user-attachments/assets/a035b625-c2bf-4361-adb6-214216bcff3d" />
Users enter their email to start the subscription process.

## Unsubscribe Page
<img width="416" height="608" alt="image" src="https://github.com/user-attachments/assets/84752fcf-abb5-427a-93ee-0cf103c74ffe" />
Users can easily opt-out with a similar verification process.

## Sample XKCD Email
<img width="477" height="506" alt="image" src="https://github.com/user-attachments/assets/0a72ad8f-4497-48f1-b9d9-34b78253f7d5" />
A daily dose of humor delivered right to the inbox.


## ✨ Features

  * 📧 **Two-Step Email Verification:** Users subscribe by entering their email and confirming with a 6-digit code sent to their inbox. This ensures a secure and validated subscriber list.
  * 🤣 **Daily Random XKCD Comic:** A CRON job runs automatically every 24 hours to fetch a new random XKCD comic and send it to all registered subscribers.
  * 👋 **Verified Unsubscribe:** Users can easily and securely unsubscribe through a link in the email, which also requires a verification code to prevent accidental removal.
  * 📄 **File-Based "Database":** All subscriber emails are stored and managed in a simple `registered_emails.txt` file, adhering to the project requirements of not using a traditional database.
  * 🤖 **Automated CRON Job Setup:** Includes a `setup_cron.sh` script that automatically configures the system's crontab to run the daily email script.
  * 🌐 **Resilient Comic Fetching:** The system uses PHP cURL to fetch comic data from the XKCD API and includes a fallback to `file_get_contents` to ensure reliability.

-----

## 🛠️ Tech Stack

  * **Backend:** PHP
  * **Frontend:** HTML5 & CSS
  * **Server Automation:** Bash Script (for CRON setup)

-----

## 🚀 Getting Started

Follow these steps to run the project on your local machine.

### Prerequisites

  * A local web server environment (e.g., XAMPP, WAMP, MAMP)
  * PHP (Version 8.3 recommended)
  * A local email testing tool like [Mailpit](https://mailpit.axllent.org/) to capture and view outgoing emails.

### Installation & Setup

1.  **Clone the repository:**

    ```sh
    git clone https://github.com/ANISHAGRWAL/rtCamp-assignment.git
    ```

2.  **Navigate to the project directory:**

    ```sh
    cd src
    ```

3.  **Place the project** in your local server's web root (e.g., `htdocs` in XAMPP).

4.  **Configure Email Testing:**

      * Set up Mailpit or a similar tool.
      * Configure your `php.ini` file to use the mail-catching tool by setting the `sendmail_path`.

5.  **Set up the CRON Job:**

      * Open your terminal in the `src` directory.
      * Make the setup script executable:
        ```sh
        chmod +x setup_cron.sh
        ```
      * Run the script to automatically add the daily task to your system's crontab:
        ```sh
        ./setup_cron.sh
        ```

    This will schedule `cron.php` to run every day at 9:00 AM. Logs will be stored in `cron.log`.

6.  **Start Subscribing\!**

      * Open your browser and navigate to `http://localhost/your-project-folder/src/`.
      * Enter your email, receive the verification code in Mailpit, and complete your subscription.

