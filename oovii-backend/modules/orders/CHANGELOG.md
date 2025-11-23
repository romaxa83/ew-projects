# [7.0.2]
- Bug fix: после удаления товара с корзины не обновлялся массив с закешированными id товаров

# [3.3.1]
- Удалены getter-ы `formatted_*` в Order && OrderItem
- Переименованы методы в `CheckoutStorage` и `CheckoutStorageInterface` перенесен в контракты 
- Улучшена работа с `CartCondition`-ами. Теперь можно добавлять глобальные условия `DatabaseStorage::addGlobalCondition(new LoyaltyProgramCondition());`
