<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            // Miliciano
            ['name' => 'BRUTTAL', 'rank' => 'Miliciano'],
            ['name' => 'chupetedeballena', 'rank' => 'Miliciano'],
            ['name' => 'DoppelyJr', 'rank' => 'Miliciano'],
            ['name' => 'ELLOCO0132', 'rank' => 'Miliciano'],
            ['name' => 'Elmogus25', 'rank' => 'Miliciano'],
            ['name' => 'ElMsiLow', 'rank' => 'Miliciano'],
            ['name' => 'Escarlatus', 'rank' => 'Miliciano'],
            ['name' => 'Essedz', 'rank' => 'Miliciano'],
            ['name' => 'FelipeElCapo', 'rank' => 'Miliciano'],
            ['name' => 'Fortrest', 'rank' => 'Miliciano'],
            ['name' => 'gabo096', 'rank' => 'Miliciano'],
            ['name' => 'GabrielArzola', 'rank' => 'Miliciano'],
            ['name' => 'JMBC2048', 'rank' => 'Miliciano'],
            ['name' => 'KEMPACHI83', 'rank' => 'Miliciano'],
            ['name' => 'Mercenario831', 'rank' => 'Miliciano'],
            ['name' => 'NEGAN85', 'rank' => 'Miliciano'],
            ['name' => 'Rumpelsinski', 'rank' => 'Miliciano'],
            ['name' => 'SebaCid', 'rank' => 'Miliciano'],
            ['name' => 'Stankoz', 'rank' => 'Miliciano'],
            ['name' => 'vistian15', 'rank' => 'Miliciano'],
            ['name' => 'YumiYumy', 'rank' => 'Miliciano'],

            // Artesano
            ['name' => 'ArvelWolf', 'rank' => 'Artesano'],
            ['name' => 'chessplayerd', 'rank' => 'Artesano'],
            ['name' => 'Kransten', 'rank' => 'Artesano'],
            ['name' => 'LordJudicious', 'rank' => 'Artesano'],
            ['name' => 'MARCANOALEXCCS', 'rank' => 'Artesano'],
            ['name' => 'RodricAybara', 'rank' => 'Artesano'],

            // Soldado
            ['name' => 'arelyUwU', 'rank' => 'Soldado'],
            ['name' => 'ArutM7', 'rank' => 'Soldado'],
            ['name' => 'ASLET', 'rank' => 'Soldado'],
            ['name' => 'Benjitafomeql', 'rank' => 'Soldado'],
            ['name' => 'braian11', 'rank' => 'Soldado'],
            ['name' => 'Davincho3103', 'rank' => 'Soldado'],
            ['name' => 'Deykrex', 'rank' => 'Soldado'],
            ['name' => 'GALANPIGMEO', 'rank' => 'Soldado'],
            ['name' => 'Hecue', 'rank' => 'Soldado'],
            ['name' => 'Iliasora', 'rank' => 'Soldado'],
            ['name' => 'IronMaxCL', 'rank' => 'Soldado'],
            ['name' => 'iShun', 'rank' => 'Soldado'],
            ['name' => 'J01X', 'rank' => 'Soldado'],
            ['name' => 'Jefejaching', 'rank' => 'Soldado'],
            ['name' => 'Joserro', 'rank' => 'Soldado'],
            ['name' => 'Juanesneik', 'rank' => 'Soldado'],
            ['name' => 'kesoamarillo', 'rank' => 'Soldado'],
            ['name' => 'kevtau', 'rank' => 'Soldado'],
            ['name' => 'KratosTSS', 'rank' => 'Soldado'],
            ['name' => 'maxisilvab', 'rank' => 'Soldado'],
            ['name' => 'MaxpectronD', 'rank' => 'Soldado'],
            ['name' => 'nathanshaune', 'rank' => 'Soldado'],
            ['name' => 'Odiseo9', 'rank' => 'Soldado'],
            ['name' => 'ShadeKali', 'rank' => 'Soldado'],
            ['name' => 'Sigridda', 'rank' => 'Soldado'],
            ['name' => 'spreir', 'rank' => 'Soldado'],
            ['name' => 'SrMatiMax', 'rank' => 'Soldado'],

            // Guardian
            ['name' => 'Akrondar', 'rank' => 'Guardian'],
            ['name' => 'AlessandroM', 'rank' => 'Guardian'],
            ['name' => 'Bethelion', 'rank' => 'Guardian'],
            ['name' => 'Chitooxp', 'rank' => 'Guardian'],
            ['name' => 'darknet28', 'rank' => 'Guardian'],
            ['name' => 'Neuro3044N', 'rank' => 'Guardian'],
            ['name' => 'octyagiz', 'rank' => 'Guardian'],
            ['name' => 'OjitosC', 'rank' => 'Guardian'],
            ['name' => 'REINORIX', 'rank' => 'Guardian'],
            ['name' => 'Rw0lf', 'rank' => 'Guardian'],
            ['name' => 'salsu23', 'rank' => 'Guardian'],
            ['name' => 'Sniperpro13', 'rank' => 'Guardian'],
            ['name' => 'SrMaxum', 'rank' => 'Guardian'],
            ['name' => 'Valardul', 'rank' => 'Guardian'],
            ['name' => 'WildDrake', 'rank' => 'Guardian'],
            ['name' => 'YoelTC', 'rank' => 'Guardian'],
            ['name' => 'ZaZa1101101', 'rank' => 'Guardian'],

            // Mercader
            ['name' => 'bloqueadoporbobi', 'rank' => 'Mercader'],
            ['name' => 'CesarJM', 'rank' => 'Mercader'], // Este será admin
            ['name' => 'Doctor03', 'rank' => 'Mercader'],
            ['name' => 'GotTheTrue', 'rank' => 'Mercader'],
            ['name' => 'SrPsychoo', 'rank' => 'Mercader'],
            ['name' => 'TenientePanque', 'rank' => 'Mercader'],
            ['name' => 'TomasGr', 'rank' => 'Mercader'],

            // Templario
            ['name' => 'Eliandoski', 'rank' => 'Templario'],
            ['name' => 'HEIEN', 'rank' => 'Templario'],
            ['name' => 'HEIENEV', 'rank' => 'Templario'],
            ['name' => 'Rigardus', 'rank' => 'Templario'],
            ['name' => 'SteinerSD', 'rank' => 'Templario'],
        ];

        foreach ($users as $userData) {
            $role = ($userData['name'] === 'CesarJM') ? 'admin' : 'user';

            DB::table('users')->insert([
                'name' => $userData['name'],
                'email' => null, // Email es nullable
                'email_verified_at' => Carbon::now(), // Verificado para estos usuarios seed
                'password' => Hash::make(strtolower($userData['name'])), // Contraseña es el mismo nombre de usuario en minúsculas
                'role' => $role,
                'rank' => $userData['rank'],
                'language_preference' => 'es', // Valor por defecto 'es'
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
