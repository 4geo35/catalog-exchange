<x-tt::modal.confirm wire:model="displayDelete">
    <x-slot name="title">Удалить файл</x-slot>
    <x-slot name="text">Будет невозможно восстановить файл!</x-slot>
</x-tt::modal.confirm>

<x-tt::modal.confirm wire:model="displayRun" closeAction="closeRun" confirmAction="confirmRun">
    <x-slot name="title">Запустить обработку файла</x-slot>
    <x-slot name="text">Начнется процесс создания\обновления каталога!</x-slot>
</x-tt::modal.confirm>

<x-tt::modal.confirm wire:model="displayStop" closeAction="closeStop" confirmAction="confirmStop">
    <x-slot name="title">Остановить обработку файла</x-slot>
    <x-slot name="text">Остановится процесс создания\обновления каталога!</x-slot>
</x-tt::modal.confirm>


<x-tt::modal.confirm wire:model="displayClearHistory" closeAction="closeClearHistory" confirmAction="confirmClearHistory">
    <x-slot name="title">Очистить историю обработки файла</x-slot>
    <x-slot name="text">Будет очищена история обработки файла!</x-slot>
</x-tt::modal.confirm>
