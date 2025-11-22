#!/bin/bash

readonly test="test"
readonly prod="prod"

info(){
	infostr="\e[34m [х]-- $1\e[0m";
	echo -e $infostr;
}

success(){
	infostr="\e[32m [х]-- $1\e[0m";
	echo -e $infostr;
}

error(){
	infostr="\e[31m [х]-- $1\e[0m";
	echo -e $infostr;
}

function usage(){
  echo "Скрипт для импорта дампа бд с хоста и экспорта его в бд докер образа (контейнеры перезапущены)"
	echo "Использование: $0 [-h help][--help help] [-p product] [-i import]"
	echo "Examples :"
	echo "$0"
	echo "стягиваем с прода"
	echo "$0 -p"
	echo "только import"
	echo "$0 -i"
	exit 1
}

TYPEHOST=$test
EXPORT=0

while [ $# -gt 0 ]
do
	case $1 in
		-h|--help )
			shift
			usage
			;;
		-p )
			TYPEHOST=$prod
			shift
			;;
		-i )
			EXPORT=1
			shift
			;;
		* )
			usage
			;;
	esac
done

SCRIPT=`realpath -s $0`
SCRIPTPATH=`dirname $SCRIPT`

#формат даты
DATA=`date +%Y-%m-%d_%H`
# путь к дампу
PATHDUMP="${SCRIPTPATH}/${DATA}_${TYPEHOST}_mysqldump.sql"
# путь к данным бд
PATHDBDATA="${SCRIPTPATH}/storage/db"
#ключи mysqldump
OPTS="--add-drop-database --add-locks --skip-tz-utc --create-options --disable-keys --extended-insert --single-transaction --quick --set-charset --routines --events --triggers --comments --quote-names --order-by-primary --hex-blob"

info "Выгружаем dump.sql ${PATHDUMP}";

STARTIMPORT=$(date +%s)
if [ "$TYPEHOST" == $test ];then
  # stage
#  mysqldump -v ${OPTS} -h 195.201.39.33 -u arma_db_u -pPRgarMa3zSHHvEJB4nNrSIPa arma_db --ignore-table=arma_db.telescope_entries --ignore-table=arma_db.telescope_entries_tags > ${PATHDUMP}
  mysqldump -v -h 23.88.110.27 -u arma_db_u -pPRgarMa3zSHHvEJB4nNrSIPa arma_db --ignore-table=arma_db.telescope_entries --ignore-table=arma_db.telescope_entries_tags > ${PATHDUMP}
else
  # prod
  mysqldump -v ${OPTS} -h 135.181.25.186 -u armaback_db_u -p1oONNJ01wy7abO1M armaback_db > ${PATHDUMP}
fi
ENDIMPORT=$(date +%s)

# проверяем первую и последнюю строки дампа, если все норм продолжаем, если нет удаляем дамп и выходим из скрипта
BEGIN=`head -n 1 ${PATHDUMP} | grep ^'-- MySQL dump' | wc -l`
END=`tail -n 1 ${PATHDUMP} | grep ^'-- Dump completed' | wc -l`

if [ "$BEGIN" == "1" ];then
  if [ "$END" == "1" ];then
    success "Конец дампа OK"
  else
    error "Конец дампа FAIL"
    rm ${PATHDUMP}
    error "Удален ${PATHDUMP}"
    error "Остановка скрипта"
    exit
  fi
  success "Начало дампа OK"
else
    error "Начало дампа FAIL"
    error "Удален ${PATHDUMP}"
    rm ${PATHDUMP}
    error "Остановка скрипта"
    exit
fi

# если только импорт, то данный блок не выполняется
if [ $EXPORT -ne 1 ]; then
  info "Останавливаем контейнеры";
  docker-compose down

  info "Удаляем данные из старой бд";
  sudo rm -rf $PATHDBDATA

  info "Подымаем контейнеры";
  docker-compose up -d

  sleep 35s

  info "Накатываем dump в бд";
  STARTEXPORT=$(date +%s)
  mysql -v -h 192.168.177.1 -u root -proot am_db < $PATHDUMP
  ENDEXPORT=$(date +%s)
  # рассчитываем время export дампа
  DIFFEXPORT=$(( $ENDEXPORT - $STARTEXPORT ))
  info "Time EXPORT dump - ${DIFFEXPORT} sec."
fi

# рассчитываем время import дампа
DIFFIMPORT=$(( $ENDIMPORT - $STARTIMPORT ))
info "Time IMPORT dump - ${DIFFIMPORT} sec."
# размер файла
FILESIZE=$(stat -c%s "${PATHDUMP}")
FILESIZEMB=$(bc<<<"scale=2;$FILESIZE/1000000")
info "Size of ${PATHDUMP} = ${FILESIZEMB} MB"
