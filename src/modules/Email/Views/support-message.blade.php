@extends('Email::layout')

@section('content')
    <div class="b-container">
        <div class="b-panel">
            <h3 class="email-headline"><strong>Новое сообщение из формы поддержки</strong></h3>

            <div class="b-table-wrap">
                <table class="b-table" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="label">Имя</td>
                        <td class="val">{{ $name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Email</td>
                        <td class="val">{{ $email }}</td>
                    </tr>
                </table>
            </div>

            <div class="mt20">
                <p><strong>Сообщение:</strong></p>
                <p>{!! nl2br(e($supportMessage)) !!}</p>
            </div>
        </div>
    </div>
@endsection
