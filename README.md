# 0x45
→ E : a micro forum 

![preview img](https://github.com/user-attachments/assets/88033f9e-f83c-4caf-aaa8-7824852ed817)

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
- run the `./src/Model/utils/init.sql` against your mariadb/mysql service
  - if you want to seed, also run `php ./src/Model/utils/seed.php`
- setup your .env
- serve via apache || php -S || w/e

## DB model
![model img](https://github.com/user-attachments/assets/7de252e5-c4fe-4c7d-af5f-a8ea36cfec30)
