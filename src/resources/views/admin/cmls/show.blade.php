<x-admin-layout>
    <x-slot name="title">Обмен {{ $cml->id }}</x-slot>
    <x-slot name="pageTitle">Обмен {{ $cml->id }}</x-slot>

    <livewire:cexc-admin-cml-file-list :$cml />
</x-admin-layout>
