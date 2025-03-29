# Solar Logger

![application overview](/docs/overview.png)

**Solar Logger** is a specialized backend application built with Laravel, designed to continuously collect, process, and securely transmit data from a private solar energy system to an external monitoring application.

## Core Functionalities

- **Automated Data Retrieval**: Regular acquisition of current solar data through proprietary inverter APIs using a custom-developed Python script.
- **Data Integration & Local Storage**: Laravel-based backend analyzes and stores retrieved data in a local MySQL database.
- **Secure Data Transfer**: An export module synchronizes data via a REST API interface with an external Laravel application ([solar_watcher](https://github.com/TobMoeller/solar_watcher)).
- **Queue Management**: A Redis powered Laravel Queue is employed for efficient handling of export jobs, ensuring reliable and performant synchronization.
- **Automatic Notifications**: Email alert system notifying about outages or communication issues with individual inverters.

