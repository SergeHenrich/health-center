@extends('layouts.app')
@section('title', 'Rapports')
@section('page-title', 'Tableau de bord des rapports')

@section('content')
@include('coming-soon', ['pageTitle' => 'Rapports et statistiques', 'message' => 'Le module de rapports est en cours de développement. Il permettra de générer des statistiques et des exports.'])
@endsection
