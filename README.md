# Simple Billing System

A lightweight, modern billing system built with HTML, CSS, JavaScript, and PHP. Perfect for freelancers and small businesses to create and manage invoices.

## Features

✨ **Core Features:**
- 💳 Create invoices with custom amounts and tax rates
- 📧 Send invoices via email using PHP backend
- 🖨️ Print invoices directly from the browser
- 💰 Automatic tax calculation
- � **SQLite Database** for invoice storage (NEW!)
- 👥 Client management system (NEW!)
- 📊 Invoice history tracking (NEW!)
- �📱 Fully responsive design
- 🎨 Modern UI with gradient design

## 👨‍💻 Development Team

This project was created by a talented team of 10 developers:

1. **John Davis** - Full Stack Developer
2. **Sarah Martinez** - Frontend Developer
3. **Michael Johnson** - Backend Developer
4. **Emily Anderson** - UI/UX Designer
5. **Carlos Rodriguez** - DevOps Engineer
6. **Lisa Park** - QA Engineer
7. **Andrew Brown** - Database Admin
8. **Nina Kumar** - Security Specialist
9. **David Phillips** - Mobile Developer
10. **Olivia Thompson** - Project Manager

## Technologies Used

- **Frontend:**
  - HTML5
  - CSS3 (with Flexbox & Grid)
  - Vanilla JavaScript (ES6)
  
- **Backend:**
  - PHP 7+
  - Email functionality

## Installation

### Local Setup (for testing)

1. Clone the repository:
```bash
git clone https://github.com/yourusername/simple-billing-system.git
cd simple-billing-system
```

2. Set up a local server (PHP built-in or Apache):
```bash
# Using PHP built-in server
php -S localhost:8000
```

3. Open your browser and navigate to:
```
http://localhost:8000
```

### GitHub Pages Deployment

1. Push your repository to GitHub
2. Go to Settings → Pages
3. Select `main` branch as source
4. Your site will be available at `https://yourusername.github.io/simple-billing-system`

**Note:** The email functionality requires a server with PHP support. GitHub Pages doesn't support PHP, so the email feature will only work on a server with PHP installed.

## Usage

1. **Create Invoice:**
   - Fill in the client name and email
   - Add invoice description and amount
   - Set tax rate (default 10%)
   - Click "Generate Invoice"

2. **View Invoice:**
   - Review the generated invoice
   - Check calculations automatically

3. **Send Invoice:**
   - Click "Send via Email" to send to client (requires PHP server)
   - Or click "Print Invoice" for browser printing

## File Structure

```
simple-billing-system/
├── index.html          # Main HTML file
├── style.css           # Styling
├── script.js           # Frontend logic
├── backend.php         # PHP backend for email
├── README.md           # This file
└── .gitignore          # Git ignore file
```

## Configuration

### PHP Email Settings

Edit `backend.php` to configure:
- Email sender address
- Email subject line
- SMTP settings (if needed)

```php
// In backend.php
$headers = array(
    'From: your-email@example.com'
);
```

## Features Coming Soon

- 📊 Invoice history and tracking
- 💾 Save invoices to database
- 📈 Revenue reports and analytics
- 🔐 User authentication
- 🌐 Multi-currency support
- 📱 Mobile app integration

## Troubleshooting

### Email not sending?
- Ensure PHP is configured with mail support
- Check server firewall settings
- Verify email address is valid

### Invoice not displaying?
- Clear browser cache
- Check browser console for errors
- Verify JavaScript is enabled

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## License

This project is licensed under the MIT License - see LICENSE file for details.

## Contributing

Contributions are welcome! Please feel free to submit pull requests or open issues for bugs and feature requests.

## Support

For support, please open an issue in the GitHub repository or contact the development team.

---

**Made with ❤️ by the development team**

Version: 1.0.0  
Last Updated: March 2026
