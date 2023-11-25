@extends('errors::minimal')

@section('title', config('app.name'))
@section('code', '503')
@section('message', __('メンテナンス中です'))
