<?php

namespace App\Enum;

enum OrderStatus: string
{
    case En_Attente = 'en_attente';
    case Payer = 'payer';
    case Expedier = 'expedier';
    case Annuler = 'annuler';
}