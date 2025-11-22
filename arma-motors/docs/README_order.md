<div id="order-top"></div>
## Модуль заявки (order)
<p align="left">(<a href="https://bitbucket.org/wezom/arma-motors/src/develop/#top">back to main</a>)</p>

##### Схема связей
<img src="diagrams/order/relations.png" alt="order-relations">

<p>
    При создании заявки, в таб. Order все поля обязательны (кроме admin, он устанавливается в админке),
    в таб. additions - записываются доп. данные по заявки, для каждого типа заявки
    (кузовной, страховка, то ...) они разные
</p>

<!-- TABLE OF CONTENTS -->
<ol>
    <li><a href="#creat-order-flow">Получение данных при создании заявки для МП</a></li>
    <li><a href="#creat-order-body">Действия при создании заявки на кузовной</a></li>
    <li><a href="#free-time">Свободные слоты времени</a></li>
</ol>

##### Create flow
<div id="creat-order-flow"></div>
<img src="diagrams/order/flow/credit-order.png" alt="credit-order">

<img src="diagrams/order/flow/insurance-order.png" alt="insurance-order">
<p align="right">(<a href="#order-top">back to top</a>)</p>

#### Flow по созданию заявки на кузовной ремонт
<div id="creat-order-body"></div>
<img src="diagrams/order/mutations/create_body_order.png" alt="create-body-order">
<p align="right">(<a href="#order-top">back to top</a>)</p>

#### Свободные слоты времени
<div id="free-time"></div>
Процесс получения данных и расчет свободного времени (в разработке)
<img src="diagrams/order/flow/free_time_flow.png" alt="free-time-flow">

Связи данных
<img src="diagrams/order/free_time_relations.png" alt="free-time-relations">

<p align="right">(<a href="#order-top">back to top</a>)</p>
