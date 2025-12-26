<?php

declare(strict_types=1);

namespace App\Controller\Api\Admin\Vue;

use App\Controller\SingleActionInterface;
use App\Entity\Enums\AdCategories;
use App\Entity\Enums\AdMediaType;
use App\Entity\Enums\AdStatus;
use App\Http\Response;
use App\Http\ServerRequest;
use Psr\Http\Message\ResponseInterface;

/**
 * Proporciona los datos necesarios para el formulario de anuncios en Vue.
 */
final class AdvertisementsAction implements SingleActionInterface
{
    public function __invoke(
        ServerRequest $request,
        Response $response,
        array $params
    ): ResponseInterface {
        return $response->withJson([
            'categories' => AdCategories::getOptions(),
            'mediaTypes' => AdMediaType::getOptions(),
            'statuses' => AdStatus::getOptions(),
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
            'spanishProvinces' => self::getSpanishProvinces(),
        ]);
    }

    /**
     * Lista de provincias de España para segmentación.
     */
    private static function getSpanishProvinces(): array
    {
        return [
            'Álava' => 'Álava',
            'Albacete' => 'Albacete',
            'Alicante' => 'Alicante',
            'Almería' => 'Almería',
            'Asturias' => 'Asturias',
            'Ávila' => 'Ávila',
            'Badajoz' => 'Badajoz',
            'Barcelona' => 'Barcelona',
            'Burgos' => 'Burgos',
            'Cáceres' => 'Cáceres',
            'Cádiz' => 'Cádiz',
            'Cantabria' => 'Cantabria',
            'Castellón' => 'Castellón',
            'Ciudad Real' => 'Ciudad Real',
            'Córdoba' => 'Córdoba',
            'Cuenca' => 'Cuenca',
            'Gerona' => 'Gerona',
            'Granada' => 'Granada',
            'Guadalajara' => 'Guadalajara',
            'Guipúzcoa' => 'Guipúzcoa',
            'Huelva' => 'Huelva',
            'Huesca' => 'Huesca',
            'Islas Baleares' => 'Islas Baleares',
            'Jaén' => 'Jaén',
            'La Coruña' => 'La Coruña',
            'La Rioja' => 'La Rioja',
            'Las Palmas' => 'Las Palmas',
            'León' => 'León',
            'Lérida' => 'Lérida',
            'Lugo' => 'Lugo',
            'Madrid' => 'Madrid',
            'Málaga' => 'Málaga',
            'Murcia' => 'Murcia',
            'Navarra' => 'Navarra',
            'Orense' => 'Orense',
            'Palencia' => 'Palencia',
            'Pontevedra' => 'Pontevedra',
            'Salamanca' => 'Salamanca',
            'Santa Cruz de Tenerife' => 'Santa Cruz de Tenerife',
            'Segovia' => 'Segovia',
            'Sevilla' => 'Sevilla',
            'Soria' => 'Soria',
            'Tarragona' => 'Tarragona',
            'Teruel' => 'Teruel',
            'Toledo' => 'Toledo',
            'Valencia' => 'Valencia',
            'Valladolid' => 'Valladolid',
            'Vizcaya' => 'Vizcaya',
            'Zamora' => 'Zamora',
            'Zaragoza' => 'Zaragoza',
        ];
    }
}
