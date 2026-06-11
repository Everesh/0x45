# 0x45
→ E : a micro forum 

![preview img](https://github.com/user-attachments/assets/e7f39aa7-190c-4072-b55f-4d85d61a600e)

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
![model img](https://github.com/user-attachments/assets/db4454a7-9554-407c-8301-fdbba1fffd72)
