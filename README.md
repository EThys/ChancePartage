<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**


## Routes pour les API
**📌Pour l'authentification**

GET:	/api/users/all	Récupérer tous les utilisateurs
POST:	/api/register	Inscription d'un utilisateur
POST:	/api/login	Connexion d'un utilisateur
GET:	/api/user/{id}	Récupérer un utilisateur par son ID
POST:	/api/logout	Déconnexion d'un utilisateur

**content json**
**- api/register**
{
  "last_name": "Muzola",
  "first_name": "Ethberg",
  "email": "ethbergmuzola500@gmail.com",
  "password": "password123",
  "gender": "masculin",
  "phone": "+243854434602",
  "profession": "Développeur",
  "date_of_birth": "1990-01-01",
  "nationality": "Congolaise",
  "current_city": "Kinshasa"
}

**- api/login**
{
  "identifiant": "ethbergmuzola500@gmail.com", L'utilisateur peut se connecter avec son email ou son numéro de téléphone.
  "password": "password123"
}

**📌Pour les events**

GET:	/api/events/all	Récupérer tous les events
POST:	/api/events	Création d'un event
{
  "name": "Concert",
  "description": "Un concert incroyable",
  "event_date": "2023-12-25",
  "max_participants": 1000,
  "status": "open"
}
PUT:	/api/events/{id}	Modification d'un event
{
  "name": "Concert Majeur",
  "description": "Un concert encore plus incroyable",
  "event_date": "2023-12-26",
  "max_participants": 1500,
  "status": "closed"
}
DELETE:	/api/events/{id}	Suppression d'un event
GET:	/api/events/{id}	Récupérer un event en particulier

**📌Pour les reservations**

GET:	/api/reservations/all	Récupérer toutes les reservations
POST:	/api/events/{eventId}/reservation	Création d'une reservation
{
    "amount": 50.00
}
PUT:	/api/reservations/{reservationId}	Modification d'une reservation
DELETE:	/api/reservations/{reservationId}	Suppression d'une reservation
GET:	/api/reservations/{reservationId}	Récupérer une reservation en particulier

**📌Pour les winners(tirage)**

GET:	/api/winners/all	Récupérer tous les gagnants
POST:	/api/winners/{eventId}/tirage	Faire un tirage
GET:	/api/winners/{eventId}	Récupérer tous les gagnants pour un event x


## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
