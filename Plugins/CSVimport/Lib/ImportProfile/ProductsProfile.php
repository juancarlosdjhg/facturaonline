<?php
/**
 * Copyright (C) 2020-2021 Carlos Garcia Gomez <carlos@facturascripts.com>
 */
namespace FacturaScripts\Plugins\CSVimport\Lib\ImportProfile;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Dinamic\Model\Fabricante;
use FacturaScripts\Dinamic\Model\Familia;
use FacturaScripts\Dinamic\Model\Producto;
use FacturaScripts\Dinamic\Model\Stock;

/**
 * Description of ProductsProfile
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 */
class ProductsProfile extends ProfileClass
{

    /**
     * 
     * @var Familia[]
     */
    protected $families = [];

    /**
     * 
     * @var Fabricante[]
     */
    protected $manufacturers = [];

    /**
     * 
     * @return array
     */
    public function getDataFields(): array
    {
        return [
            'productos.referencia' => ['title' => 'reference'],
            'productos.descripcion' => ['title' => 'description'],
            'productos.observaciones' => ['title' => 'observations'],
            'productos.codfabricante' => ['title' => 'manufacturer'],
            'productos.codfamilia' => ['title' => 'family'],
            'productos.codimpuesto' => ['title' => 'tax-code'],
            'variantes.codbarras' => ['title' => 'barcode'],
            'variantes.precio' => ['title' => 'price'],
            'variantes.coste' => ['title' => 'cost-price'],
            'stocks.cantidad' => ['title' => 'stock']
        ];
    }

    /**
     * 
     * @param string $code
     * @param int    $len
     *
     * @return string
     */
    protected function cleanCode($code, $len): string
    {
        $table = [
            'Š' => 'S', 'š' => 's', 'Đ' => 'Dj', 'đ' => 'dj', 'Ž' => 'Z', 'ž' => 'z', 'Č' => 'C', 'č' => 'c', 'Ć' => 'C', 'ć' => 'c',
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
            'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O',
            'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e',
            'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o',
            'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'ý' => 'y', 'þ' => 'b',
            'ÿ' => 'y', 'Ŕ' => 'R', 'ŕ' => 'r',
        ];
        $text = \preg_replace('/^[A-Z0-9_\+\.\-]/', '', \strtr($code, $table));
        return \strlen($text) > $len ? \substr($text, 0, $len) : $text;
    }

    /**
     * 
     * @param string $codfamilia
     *
     * @return bool
     */
    protected function findFamily(&$codfamilia): bool
    {
        if (empty($codfamilia)) {
            return false;
        }

        /// find in cache
        $newDescripcion = $this->toolBox()->utils()->noHtml($codfamilia);
        $newCodfamilia = $this->cleanCode($newDescripcion, 8);
        foreach ($this->families as $fam) {
            if ($fam->codfamilia === $codfamilia || $fam->codfamilia === $newCodfamilia || $fam->descripcion === $newDescripcion) {
                $codfamilia = $fam->codfamilia;
                return true;
            }
        }

        /// find in database
        $family = new Familia();
        $where = [
            new DataBaseWhere('codfamilia', $codfamilia),
            new DataBaseWhere('codfamilia', $newCodfamilia, '=', 'OR'),
            new DataBaseWhere('descripcion', $newDescripcion, '=', 'OR')
        ];
        if ($family->loadFromCode('', $where)) {
            $codfamilia = $family->codfamilia;
            $this->families[$codfamilia] = $family;
            return true;
        }

        /// create family
        $family->codfamilia = $newCodfamilia;
        $family->descripcion = $newDescripcion;
        if ($family->save()) {
            $codfamilia = $family->codfamilia;
            $this->families[$codfamilia] = $family;
            return true;
        }

        return false;
    }

    /**
     * 
     * @param string $codfabricante
     *
     * @return bool
     */
    protected function findManufacturer(&$codfabricante): bool
    {
        if (empty($codfabricante)) {
            return false;
        }

        /// find in cache
        $newNombre = $this->toolBox()->utils()->noHtml($codfabricante);
        $newCodfabricante = $this->cleanCode($codfabricante, 8);
        foreach ($this->manufacturers as $man) {
            if ($man->codfabricante == $codfabricante || $man->codfabricante == $newCodfabricante || $man->nombre == $newNombre) {
                $codfabricante = $man->codfabricante;
                return true;
            }
        }

        /// find in database
        $manufacturer = new Fabricante();
        $where = [
            new DataBaseWhere('codfabricante', $codfabricante),
            new DataBaseWhere('codfabricante', $newCodfabricante, '=', 'OR'),
            new DataBaseWhere('nombre', $newNombre, '=', 'OR')
        ];
        if ($manufacturer->loadFromCode('', $where)) {
            $codfabricante = $manufacturer->codfabricante;
            $this->manufacturers[$codfabricante] = $manufacturer;
            return true;
        }

        /// create manufacturer
        $manufacturer->codfabricante = $newCodfabricante;
        $manufacturer->nombre = $newNombre;
        if ($manufacturer->save()) {
            $codfabricante = $manufacturer->codfabricante;
            $this->manufacturers[$codfabricante] = $manufacturer;
            return true;
        }

        return false;
    }

    /**
     * 
     * @param array $item
     *
     * @return bool
     */
    protected function importItem(array $item): bool
    {
        $where = [];
        if (isset($item['productos.referencia']) && !empty($item['productos.referencia'])) {
            $where[] = new DataBaseWhere('referencia', $item['productos.referencia']);
        } elseif (isset($item['productos.descripcion']) && !empty($item['productos.descripcion'])) {
            $where[] = new DataBaseWhere('descripcion', $item['productos.descripcion']);
        }

        if (empty($where)) {
            return false;
        }

        $product = new Producto();
        if ($product->loadFromCode('', $where) && $this->mode === static::INSERT_MODE) {
            return false;
        }

        $this->setModelValues($product, $item, 'productos.');

        /// empty reference?
        if (empty($product->referencia)) {
            $product->referencia = $product->newCode('referencia');
        }

        if (false === $this->findFamily($product->codfamilia)) {
            $product->codfamilia = null;
        }

        if (false === $this->findManufacturer($product->codfabricante)) {
            $product->codfabricante = null;
        }

        if ($product->save()) {
            $this->importVariant($product, $item);
            $this->importStock($product, $item);
            return true;
        }

        return false;
    }

    /**
     * 
     * @param Producto $product
     * @param array    $item
     *
     * @return bool
     */
    protected function importStock($product, $item): bool
    {
        if (empty($item['stocks.cantidad'])) {
            return true;
        }

        /// find stock
        $stockModel = new Stock();
        $where = [new DataBaseWhere('referencia', $product->referencia)];
        if ($stockModel->loadFromCode('', $where)) {
            $this->setModelValues($stockModel, $item, 'stocks.');
            return $stockModel->save();
        }

        /// new stock
        $newStock = new Stock();
        $newStock->codalmacen = $this->toolBox()->appSettings()->get('default', 'codalmacen');
        $newStock->idproducto = $product->idproducto;
        $newStock->referencia = $product->referencia;
        $this->setModelValues($newStock, $item, 'stocks.');
        return $newStock->save();
    }

    /**
     * 
     * @param Producto $product
     * @param array    $item
     *
     * @return bool
     */
    protected function importVariant($product, $item): bool
    {
        foreach ($product->getVariants() as $variant) {
            $this->setModelValues($variant, $item, 'variantes.');
            return $variant->save();
        }

        return false;
    }
}
