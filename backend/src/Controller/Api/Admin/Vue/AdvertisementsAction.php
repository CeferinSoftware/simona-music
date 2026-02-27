<?php

declare(strict_types=1);

namespace App\Controller\Api\Admin\Vue;

use App\Controller\SingleActionInterface;
use App\Entity\Enums\AdCategories;
use App\Entity\Enums\AdMediaType;
use App\Entity\Enums\AdStatus;
use App\Entity\Station;
use App\Http\Response;
use App\Http\ServerRequest;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Proporciona los datos necesarios para el formulario de anuncios en Vue.
 */
final class AdvertisementsAction implements SingleActionInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function __invoke(
        ServerRequest $request,
        Response $response,
        array $params
    ): ResponseInterface {
        // Obtener lista de estaciones/terrazas para selección
        $stations = $this->em->createQueryBuilder()
            ->select('s.id', 's.name', 's.short_name', 's.ad_category', 's.province', 's.city', 's.is_enabled')
            ->from(Station::class, 's')
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return $response->withJson([
            'categories' => AdCategories::getOptions(),
            'mediaTypes' => AdMediaType::getOptions(),
            'statuses' => AdStatus::getOptions(),
            'stations' => $stations,
            'weekDays' => [
                1 => 'Lunes',
                2 => 'Martes',
                3 => 'Miércoles',
                4 => 'Jueves',
                5 => 'Viernes',
                6 => 'Sábado',
                7 => 'Domingo',
            ],
            'priorityOptions' => [
                1 => 'Muy Baja',
                2 => 'Baja',
                3 => 'Normal',
                5 => 'Alta',
                7 => 'Muy Alta',
                10 => 'Máxima',
            ],
            'provinces' => [
                'Azua', 'Bahoruco', 'Barahona', 'Dajabón', 'Distrito Nacional',
                'Duarte', 'El Seibo', 'Elías Piña', 'Espaillat', 'Hato Mayor',
                'Hermanas Mirabal', 'Independencia', 'La Altagracia', 'La Romana',
                'La Vega', 'María Trinidad Sánchez', 'Monseñor Nouel', 'Monte Cristi',
                'Monte Plata', 'Pedernales', 'Peravia', 'Puerto Plata',
                'Samaná', 'San Cristóbal', 'San José de Ocoa', 'San Juan',
                'San Pedro de Macorís', 'Sánchez Ramírez', 'Santiago', 'Santiago Rodríguez',
                'Santo Domingo', 'Valverde'
            ],
            'cities' => [
                'Azua de Compostela', 'Neyba', 'Barahona', 'Dajabón',
                'Santo Domingo de Guzmán', 'San Francisco de Macorís',
                'El Seibo', 'Comendador', 'Moca', 'Hato Mayor del Rey',
                'Salcedo', 'Jimaní', 'Higüey', 'La Romana',
                'La Vega', 'Nagua', 'Bonao', 'Monte Cristi',
                'Monte Plata', 'Pedernales', 'Baní', 'Puerto Plata',
                'Samaná', 'San Cristóbal', 'San José de Ocoa', 'San Juan de la Maguana',
                'San Pedro de Macorís', 'Cotuí', 'Santiago de los Caballeros',
                'Sabaneta', 'Santo Domingo Este', 'Santo Domingo Oeste',
                'Santo Domingo Norte', 'Los Alcarrizos', 'Boca Chica',
                'Pedro Brand', 'Guerra', 'Mao', 'Constanza', 'Jarabacoa',
                'Sosúa', 'Cabarete', 'Villa Altagracia', 'Punta Cana',
                'Bayaguana', 'Sabana Grande de Boyá', 'Las Terrenas',
                'Villa Bisonó', 'Tamboril', 'Licey al Medio', 'Esperanza'
            ],
            'sectors' => [
                'Piantini', 'Naco', 'Evaristo Morales', 'Gazcue', 'Zona Colonial',
                'Bella Vista', 'El Vergel', 'Julieta Morales', 'La Esperilla',
                'Paraíso', 'Serrallés', 'Los Prados', 'El Millón', 'Arroyo Hondo',
                'Los Cacicazgos', 'Renacimiento', 'Quisqueya', 'Villa Juana',
                'Cristo Rey', 'Zona Universitaria', 'Gualey', 'Los Mina',
                'Villa Duarte', 'Ensanche Ozama', 'Alma Rosa', 'Los Frailes',
                'Mendoza', 'Isabelita', 'Charles de Gaulle', 'Cancino',
                'Sabana Perdida', 'Villa Mella', 'Los Alcarrizos Centro',
                'Pantoja', 'Herrera', 'Manoguayabo', 'Buenos Aires de Herrera',
                'El Tamarindo', 'San Isidro', 'La Caleta', 'Andrés',
                'Gurabo', 'Cienfuegos', 'Pontezuela', 'Puñal',
                'Los Jardines', 'Cerros de Gurabo', 'Reparto del Este',
                'Hato del Yaque', 'La Trinitaria', 'Ensanche Libertad',
                'Los Salados', 'Las Colinas', 'Thomen', 'El Embrujo',
                'Jardines del Norte', 'Cerro Alto', 'Bella Terra',
                'Bávaro', 'El Cortecito', 'Cap Cana', 'Verón'
            ],
        ]);
    }
}
