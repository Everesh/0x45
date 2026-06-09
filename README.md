# 0x45
→ E : a micro forum 

![preview img](https://github.com/user-attachments/assets/4ba36b66-6572-4fc4-90d5-3272f9ae38aa)

## Installation
### Prerequisites
- [composer](https://getcomposer.org/)
- [mariadb/mysql service](https://mariadb.org/)
### Settup
```bash
git clone https://github.com/Everesh/0x45.git && cd ./0x45
composer install
cp ./.env.example ./.env
```
- run the `./src/Model/init.sql` against your mariadb/mysql service
- setup your .env
- serve via apache || php -S || w/e

## DB model
![model img](https://github.com/user-attachments/assets/46b379a7-92ab-4223-8978-075efc8f67d4)
