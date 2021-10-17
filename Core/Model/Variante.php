<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2018-2020 Carlos García Gómez <carlos@facturascripts.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace FacturaScripts\Core\Model;

use FacturaScripts\Dinamic\Model\AtributoValor as DinAtributoValor;
use FacturaScripts\Dinamic\Model\Producto as DinProducto;
use FacturaScripts\Dinamic\Model\Cliente;
use FacturaScripts\Dinamic\Model\Stock as DinStock;

/**
 * Define method and attributes of table variantes.
 *
 * @author Cristo M. Estévez Hernández  <cristom.estevez@gmail.com>
 * @author Carlos García Gómez          <carlos@facturascripts.com>
 */
class Variante extends Base\ModelClass
{

    use Base\ModelTrait;
    use Base\ProductRelationTrait;

    /**
     * Barcode. Maximun 20 characteres.
     *
     * @var string
     */
    public $codbarras;

    /**
     * Cost price.
     *
     * @var int|float
     */
    public $coste;

    /**
     * Foreign key of table atributo_valores.
     *
     * @var int
     */
    public $idatributovalor1;

    /**
     * Foreign key of table atributo_valores.
     *
     * @var int
     */
    public $idatributovalor2;

    /**
     * Foreign key of table atributo_valores.
     *
     * @var int
     */
    public $idatributovalor3;

    /**
     * Foreign key of table atributo_valores.
     *
     * @var int
     */
    public $idatributovalor4;

    /**
     * Primary Key, autoincremental.
     *
     * @var int
     */
    public $idvariante;

    /**
     *
     * @var float
     */
    public $margen;

    /**
     *
     * @var float
     */
    public $margen1;

    /**
     * Price of the variant. Without tax.
     *
     * @var int|float
     */
    public $precio;

    /**
     * Price of the variant. Without tax.
     *
     * @var int|float
     */
    public $pvp1;

    /**
     *
     * @var float
     */
    public $margenobjetivo1;

    /**
     * Price of the variant. Without tax.
     *
     * @var int|float
     */
    public $preciorecomendado1;

    /**
     *
     * @var float
     */
    public $margen2;

    /**
     * Price of the variant. Without tax.
     *
     * @var int|float
     */
    public $pvp2;

    /**
     *
     * @var float
     */
    public $margenobjetivo2;

    /**
     * Price of the variant. Without tax.
     *
     * @var int|float
     */
    public $preciorecomendado2;

    /**
     *
     * @var float
     */
    public $margen3;

    /**
     * Price of the variant. Without tax.
     *
     * @var int|float
     */
    public $pvp3;

    /**
     *
     * @var float
     */
    public $margenobjetivo3;

    /**
     * Price of the variant. Without tax.
     *
     * @var int|float
     */
    public $preciorecomendado3;

    /**
     *
     * @var float
     */
    public $margen4;

    /**
     * Price of the variant. Without tax.
     *
     * @var int|float
     */
    public $pvp4;

    /**
     *
     * @var float
     */
    public $margenobjetivo4;

    /**
     * Price of the variant. Without tax.
     *
     * @var int|float
     */
    public $preciorecomendado4;

    /**
     *
     * @var float
     */
    public $margen5;

    /**
     * Price of the variant. Without tax.
     *
     * @var int|float
     */
    public $pvp5;

    /**
     *
     * @var float
     */
    public $margenobjetivo5;

    /**
     * Price of the variant. Without tax.
     *
     * @var int|float
     */
    public $preciorecomendado5;

    /**
     * Reference of the variant. Maximun 30 characteres.
     *
     * @var string
     */
    public $referencia;

    /**
     * Reference of the variant. Maximun 30 characteres.
     *
     * @var string
     */
    public $defaultpvp;

    /**
     * Physical stock.
     *
     * @var float|int
     */
    public $stockfis;

    /**
     * Sets default values.
     */
    public function clear()
    {
        parent::clear();
        $this->coste = 0.0;
        $this->precio = 0.0;
        $this->margen = 0.0;
        $this->margen1 = 0.0;
        $this->pvp1 = 0.0;
        $this->margen2 = 0.0;
        $this->pvp2 = 0.0;
        $this->margen3 = 0.0;
        $this->pvp3 = 0.0;
        $this->margen4 = 0.0;
        $this->pvp4 = 0.0;
        $this->margen5 = 0.0;
        $this->pvp5 = 0.0;
        $this->margenobjetivo1 = 0.0;
        $this->preciorecomendado1 = 0.0;
        $this->margenobjetivo2 = 0.0;
        $this->preciorecomendado2 = 0.0;
        $this->margenobjetivo3 = 0.0;
        $this->preciorecomendado3 = 0.0;
        $this->margenobjetivo4 = 0.0;
        $this->preciorecomendado4 = 0.0;
        $this->margenobjetivo5 = 0.0;
        $this->preciorecomendado5 = 0.0;
        $this->stockfis = 0.0;
        $this->defaultpvp = 'pvp1';
    }

    /**
     * 
     * @param string          $query
     * @param string          $fieldcode
     * @param DataBaseWhere[] $where
     *
     * @return CodeModel[]
     */
    public function codeModelSearch(string $query, string $fieldcode = '', $where = [])
    {
        $results = [];
        $field = empty($fieldcode) ? $this->primaryColumn() : $fieldcode;
        $find = $this->toolBox()->utils()->noHtml(\mb_strtolower($query, 'UTF8'));

        $sql = "SELECT v." . $field . " AS code, p.descripcion AS description, v.idatributovalor1, v.idatributovalor2, v.idatributovalor3, v.idatributovalor4"
            . " FROM " . static::tableName() . " v"
            . " LEFT JOIN " . DinProducto::tableName() . " p ON v.idproducto = p.idproducto"
            . " WHERE LOWER(v.referencia) LIKE '" . $find . "%'"
            . " OR v.codbarras = '" . $find . "'"
            . " OR LOWER(p.descripcion) LIKE '%" . $find . "%'"
            . " ORDER BY v." . $field . " ASC";

        foreach (self::$dataBase->selectLimit($sql, CodeModel::ALL_LIMIT) as $data) {
            $data['description'] = $this->getAttributeDescription(
                $data['idatributovalor1'],
                $data['idatributovalor2'],
                $data['idatributovalor3'],
                $data['idatributovalor4'],
                $data['description']
            );
            $results[] = new CodeModel($data);
        }

        return $results;
    }

    /**
     * 
     * @return string
     */
    public function description(bool $onlyAttributes = false)
    {
        $description = $onlyAttributes ? '' : $this->getProducto()->descripcion;
        return $this->getAttributeDescription(
                $this->idatributovalor1,
                $this->idatributovalor2,
                $this->idatributovalor3,
                $this->idatributovalor4,
                $description
        );
    }
    
    /**
     * 
     * @return string
     */
    public function description_eng()
    {
        $description = $this->getProducto()->description;
        return $description;
    }

    /**
     * 
     * @return bool
     */
    public function delete()
    {
        if ($this->referencia == $this->getProducto()->referencia) {
            $this->toolBox()->i18nLog()->warning('you-cant-delete-primary-variant');
            return false;
        }

        return parent::delete();
    }

    /**
     * 
     * @param int    $idAttVal1
     * @param int    $idAttVal2
     * @param int    $idAttVal3
     * @param int    $idAttVal4
     * @param string $description
     * @param string $separator1
     * @param string $separator2
     *
     * @return string
     */
    protected function getAttributeDescription($idAttVal1, $idAttVal2, $idAttVal3, $idAttVal4, $description = '', $separator1 = "\n", $separator2 = ', ')
    {
        $atributeValue = new DinAtributoValor();
        $extra = [];
        foreach ([$idAttVal1, $idAttVal2, $idAttVal3, $idAttVal4] as $id) {
            if (!empty($id) && $atributeValue->loadFromCode($id)) {
                $extra[] = $atributeValue->descripcion;
            }
        }

        /// compose text
        if (empty($description)) {
            return \implode($separator2, $extra);
        }

        return empty($extra) ? $description : \implode($separator1, [$description, \implode($separator2, $extra)]);
    }

    /**
     * This function is called when creating the model table. Returns the SQL
     * that will be executed after the creation of the table. Useful to insert values
     * default.
     *
     * @return string
     */
    public function install()
    {
        new DinProducto();
        new DinAtributoValor();

        return parent::install();
    }

    /**
     * 
     * @return float
     */
    public function priceWithTax()
    {
//        $defaultpvp = $this->defaultPvp();

        $customer = new Cliente();
        $defaultpvpcustomer = $customer->defaultPvp();

        switch ($defaultpvpcustomer) {
            case 'pvp1': 
                return $this->pvp1 * (100 + $this->getProducto()->getTax()->iva) / 100;
                break;

            case 'pvp2': 
                return $this->pvp2 * (100 + $this->getProducto()->getTax()->iva) / 100;
                break;

            case 'pvp3': 
                return $this->pvp3 * (100 + $this->getProducto()->getTax()->iva) / 100;
                break;

            case 'pvp4': 
                return $this->pvp4 * (100 + $this->getProducto()->getTax()->iva) / 100;
                break;

            case 'pvp5': 
                return $this->pvp5 * (100 + $this->getProducto()->getTax()->iva) / 100;
                break;
        }
    }

    /**
     * Returns the name of the column that is the model's primary key.
     *
     * @return string
     */
    public static function primaryColumn()
    {
        return 'idvariante';
    }

    public function defaultPvp()
    {
        return $this->defaultpvp;
    }

    /**
     * 
     * @return string
     */
    public function primaryDescriptionColumn()
    {
        return 'referencia';
    }

    /**
     * 
     * @return bool
     */
    public function save()
    {
        if ($this->margen1 > 0) {
            $newPrice = $this->coste * (100 + $this->margen1) / 100;
            $newMargen = $this->margen1;
            $this->pvp1 = \round($newPrice, DinProducto::ROUND_DECIMALS);
            $this->margen = $newMargen;
            $this->precio = $this->pvp1;
        }
        if ($this->margen2 > 0) {
            $newPrice = $this->coste * (100 + $this->margen2) / 100;
            $newMargen = $this->margen2;
            $this->pvp2 = \round($newPrice, DinProducto::ROUND_DECIMALS);
            $this->margen = $newMargen;
            $this->precio = $this->pvp2;
        }
        if ($this->margen3 > 0) {
            $newPrice = $this->coste * (100 + $this->margen3) / 100;
            $newMargen = $this->margen3;
            $this->pvp3 = \round($newPrice, DinProducto::ROUND_DECIMALS);
            $this->margen = $newMargen;
            $this->precio = $this->pvp3;
        }
        if ($this->margen4 > 0) {
            $newPrice = $this->coste * (100 + $this->margen4) / 100;
            $newMargen = $this->margen4;
            $this->pvp4 = \round($newPrice, DinProducto::ROUND_DECIMALS);
            $this->margen = $newMargen;
            $this->precio = $this->pvp4;
        }
        if ($this->margen5 > 0) {
            $newPrice = $this->coste * (100 + $this->margen5) / 100;
            $newMargen = $this->margen5;
            $this->pvp5 = \round($newPrice, DinProducto::ROUND_DECIMALS);
            $this->margen = $newMargen;
            $this->precio = $this->pvp5;
        }
        
        if (parent::save()) {
            $this->getProducto()->update();
            return true;
        }
        
        return false;

    }

    /**
     * 
     * @param float $price
     */
    public function setPriceWithTax($price)
    {
        $newPrice = (100 * $price) / (100 + $this->getProducto()->getTax()->iva);
        $this->pvp1 = \round($newPrice, DinProducto::ROUND_DECIMALS);
        $this->pvp2 = \round($newPrice, DinProducto::ROUND_DECIMALS);
        $this->pvp3 = \round($newPrice, DinProducto::ROUND_DECIMALS);
        $this->pvp4 = \round($newPrice, DinProducto::ROUND_DECIMALS);
        $this->pvp5 = \round($newPrice, DinProducto::ROUND_DECIMALS);

    }

    /**
     * Returns the name of the table that uses this model.
     *
     * @return string
     */
    public static function tableName()
    {
        return 'variantes';
    }

    /**
     * 
     * @return bool
     */
    public function test()
    {
        $utils = $this->toolBox()->utils();
        $this->referencia = $utils->noHtml($this->referencia);
        if (\strlen($this->referencia) < 1 || \strlen($this->referencia) > 30) {
            $this->toolBox()->i18nLog()->warning(
                'invalid-column-lenght',
                ['%value%' => $this->referencia, '%column%' => 'referencia', '%min%' => '1', '%max%' => '30']
            );
            return false;
        }

        $this->codbarras = $utils->noHtml($this->codbarras);
        return parent::test();
    }

    /**
     * 
     * @param string $type
     * @param string $list
     *
     * @return string
     */
    public function url(string $type = 'auto', string $list = 'List')
    {
        return $this->getProducto()->url($type);
    }

    /**
     * 
     * @param array $values
     *
     * @return bool
     */
    protected function saveInsert(array $values = [])
    {
        if (parent::saveInsert($values)) {
            /// set new stock?
            if ($this->stockfis != 0.0) {
                $stock = new DinStock();
                $stock->cantidad = $this->stockfis;
                $stock->codalmacen = $this->toolBox()->appSettings()->get('default', 'codalmacen');
                $stock->idproducto = $this->idproducto;
                $stock->referencia = $this->referencia;
                $stock->save();
            }

            return true;
        }

        return false;
    }
}
