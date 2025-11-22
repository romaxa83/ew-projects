### Разворачивание проекта:

```bash
git clone git@bitbucket.org:wezom/easyload-haulk.git
cd easyload-haulk
chmod +x wezom.sh

./wezom.sh init

cp .env.testing.example .env.testing
```

В файле : docker-compose.yml (должен появится в корне проекта)

требуется снять комментарий с блока
```bash
#networks:
#  default:
#    driver: bridge
#    ipam:
#      config:
#        - subnet: ${DOCKER_NETWORK}
```

В файле .env исправляем :
```dotenv
DOCKER_NETWORK=192.168.222.0/24
DOCKER_ADDRESS=192.168.222.1
```


Выполняем:

```bash
./wezom.sh build
```
Ожидаем пока развернутся контейнера.

Посмотреть команды управления docker контейнерами:

```bash
./wezom.sh help
```
