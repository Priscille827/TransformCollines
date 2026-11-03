<?php

use Illuminate\Support\Facades\Schedule;

// Expiration quotidienne des disponibilités (RG-011) — toutes les nuits à 2h
Schedule::command('transform:expirer-dispos')->dailyAt('02:00');

// Expiration quotidienne des besoins (RG-006) — toutes les nuits à 2h05
Schedule::command('transform:expirer-besoins')->dailyAt('02:05');

// Alerte pénurie J-3 (RG-014) — tous les jours à 8h du matin
Schedule::command('transform:alerte-penurie')->dailyAt('08:00');