<p align="center">
  <img src="https://www.seven.io/wp-content/uploads/Logo.svg" width="250" alt="seven logo" />
</p>

<h1 align="center">seven SMS &amp; Voice for Moodle</h1>

<p align="center">
  Send SMS and text-to-speech notifications to your <a href="https://moodle.org/">Moodle</a> learners and staff via the seven gateway.
</p>

<p align="center">
  <a href="LICENSE"><img src="https://img.shields.io/badge/License-MIT-teal.svg" alt="MIT License" /></a>
  <img src="https://img.shields.io/badge/Moodle-block-orange" alt="Moodle block" />
  <img src="https://img.shields.io/badge/PHP-7.4%2B-purple" alt="PHP 7.4+" />
</p>

---

## Features

- **SMS & Text-to-Voice** - Notify learners or admins via SMS or automated phone calls
- **User-Field Placeholders** - Reference any standard Moodle user column in your message body
- **Standard Block Plugin** - Drop the seven block onto any Moodle page

## Prerequisites

- A [Moodle](https://moodle.org/) installation
- A [seven account](https://www.seven.io/) with API key ([How to get your API key](https://help.seven.io/en/developer/where-do-i-find-my-api-key))

## Installation

1. Download the [latest release](https://github.com/seven-io/moodle/releases/latest/download/seven-moodle-latest.zip).
2. Copy the plugin folder into Moodle's `blocks/` directory.
3. Log in to Moodle as administrator.
4. Click the **Reload database** button to install the plugin.
5. Paste your seven API key when prompted.

You can now drop the seven.io block onto any course or dashboard page.

## Message Placeholders

Wrap any of these user columns in `{{ ... }}`:

| Placeholder | Description |
|-------------|-------------|
| `{{username}}` | Username |
| `{{firstname}}` | First name |
| `{{lastname}}` | Last name |
| `{{email}}` | Email address |
| `{{phone1}}` | Primary phone |
| `{{phone2}}` | Secondary phone |
| `{{institution}}` | Institution |
| `{{department}}` | Department |
| `{{address}}` | Postal address |
| `{{city}}` | City |
| `{{country}}` | Country |

Unresolved placeholders remain as plain text in the outgoing message.

## Support

Need help? Feel free to [contact us](https://www.seven.io/en/company/contact/) or [open an issue](https://github.com/seven-io/moodle/issues).

## License

[MIT](LICENSE)
