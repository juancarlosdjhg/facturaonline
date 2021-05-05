<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2017-2020 Carlos Garcia Gomez <carlos@facturascripts.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace FacturaScripts\Core\Controller;

use FacturaScripts\Core\Base\Controller;
use FacturaScripts\Core\Base\ControllerPermissions;
use FacturaScripts\Dinamic\Model\Page;
use FacturaScripts\Dinamic\Model\User;
use Symfony\Component\HttpFoundation\Response;
use App\Service\FileUploader;

/**
 * Controller to perform searches on the page
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 */
class ConfigColor extends Controller
{

    /**
     * This variable contains the input text as the $color parameter
     * to be used to update css
     *
     * @var string|false
     */
    public $logo;
    public $favicon;
    public $login;
    public $primaryColor;
    public $secundaryColor;
    public $tertiaryColor;

    /**
     * Results by page
     *
     * @var array
     */
    public $results;

    /**
     * More sections to search in
     *
     * @var array
     */
    public $sections;

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData()
    {
        $data = parent::getPageData();
        $data['showonmenu'] = false;
        return $data;
    }

    /**
     * Runs the controller's private logic.
     *
     * @param Response              $response
     * @param User                  $user
     * @param ControllerPermissions $permissions
     */
    public function privateCore(&$response, $user, $permissions)
    {
        parent::privateCore($response, $user, $permissions);
        $this->results = [];
        $this->sections = [];

        $this->logo = $this->request->files->get('logo');
        $this->favicon = $this->request->files->get('favicon');
        $this->login = $this->request->files->get('login');
        $primaryColor = $this->request->request->get('primaryColor', '');
        $secundaryColor = $this->request->request->get('secundaryColor', '');
        $tertiaryColor = $this->request->request->get('tertiaryColor', '');

        // $this->logo = $this->toolBox()->utils()->noHtml(\mb_strtolower($logo, 'UTF8'));
        $this->primaryColor = $this->toolBox()->utils()->noHtml(\mb_strtolower($primaryColor, 'UTF8'));
        $this->secundaryColor = $this->toolBox()->utils()->noHtml(\mb_strtolower($secundaryColor, 'UTF8'));
        $this->tertiaryColor = $this->toolBox()->utils()->noHtml(\mb_strtolower($tertiaryColor, 'UTF8'));

        if ($this->primaryColor !== '') {
            $this->updateCss();
        }
    }

    /**
     * Presform all initial searches.
     */
    protected function updateCss()
    {
        $archivo = fopen("./Dinamic/Assets/CSS/customDC.css", "w+b");    // Abrir el archivo, creándolo si no existe
        if( $archivo == false ){

        } else {
            $cssDefault = ".navbar-dark > .navbar-brand {\n";
            $cssDefault .= "    color: #dddddd !important;\n";
            $cssDefault .= "}                \n";
            $cssDefault .= ".navbar > .dropdown-item {\n";
            $cssDefault .= "    color: #dddddd !important;\n";
            $cssDefault .= "}                \n";
            $cssDefault .= ".navbar-nav > .nav-item > a {\n";
            $cssDefault .= "    color: #dddddd !important;\n";
            $cssDefault .= "}                \n";
            $cssDefault .= ".dropdown-menu > li > .dropdown-item {\n";
            $cssDefault .= "    color: #616060 !important;\n";
            $cssDefault .= "}                \n";
            $cssDefault .= ".navbar > .navbar-brand:hover {\n";
            $cssDefault .= "    color: #ffffff !important;\n";
            $cssDefault .= "}                \n";
            $cssDefault .= ".navbar > .dropdown-item:hover {\n";
            $cssDefault .= "    color: #ffffff !important;\n";
            $cssDefault .= "}                \n";
            $cssDefault .= ".navbar-nav > .nav-item > a:hover {\n";
            $cssDefault .= "    color: #ffffff !important;\n";
            $cssDefault .= "}                \n";
            $cssDefault .= ".dropdown-menu > li > .dropdown-item:hover {\n";
            $cssDefault .= "    color: #616060 !important;\n";
            $cssDefault .= "}\n";

            // Nav
            fwrite($archivo, ".bg-dark {\n");
            fwrite($archivo, "  background-color: ".$this->primaryColor." !important;\n");
            fwrite($archivo, "}\n");

            // Link
            fwrite($archivo, "a {\n");
            fwrite($archivo, "color: ".$this->primaryColor." !important;\n");
            fwrite($archivo, "}\n");

            fwrite($archivo, "a:hove {\n");
            fwrite($archivo, "  color: ".$this->tertiaryColor." !important;\n");
            fwrite($archivo, "}\n");

            // Legends
            fwrite($archivo, ".text-info {\n");
            fwrite($archivo, "  color: ".$this->tertiaryColor." !important;\n");
            fwrite($archivo, "}\n");

            // Mini Tabs
            fwrite($archivo, ".nav-pills .nav-link.active {\n");
            fwrite($archivo, "  background-color: ".$this->primaryColor." !important;\n");
            fwrite($archivo, "  color: ".$this->tertiaryColor." !important;\n");
            fwrite($archivo, "}\n");

            // Button
            fwrite($archivo, ".btn-primary {\n");
            fwrite($archivo, "  background-color: ".$this->primaryColor." !important;\n");
            fwrite($archivo, "  border-color: ".$this->secundaryColor." !important;\n");
            fwrite($archivo, "}\n");
            
            // Button Hover
            fwrite($archivo, ".btn-primary:hover {\n");
            fwrite($archivo, "  background-color: ".$this->secundaryColor." !important; ");
            fwrite($archivo, "  border-color: ".$this->secundaryColor." !important;\n");
            fwrite($archivo, "}\n");

            // Button MegaSearch
            fwrite($archivo, ".btn-mega-search:active {\n");
            fwrite($archivo, "  background-color: ".$this->secundaryColor." !important;\n");
            fwrite($archivo, "  border-color: ".$this->secundaryColor." !important;\n");
            fwrite($archivo, "}\n");

            // Menu desplegable
            fwrite($archivo, ".dropdown-item.active {\n");
            fwrite($archivo, "  background-color: ".$this->primaryColor." !important;\n");
            fwrite($archivo, "}\n");

            fwrite($archivo, ".dropdown-item:active {\n");
            fwrite($archivo, "  background-color: ".$this->primaryColor." !important;\n");
            fwrite($archivo, "}\n");

            // Input
            fwrite($archivo, ".form-control:focus{ \n");
            fwrite($archivo, "  border-color: ".$this->primaryColor." !important;\n");
            fwrite($archivo, "}\n");

            // Css por default
            fwrite($archivo, $cssDefault);           

            fflush($archivo);
        }

        fclose($archivo);

        if (!empty($this->logo)) {
            $targetPath = './Dinamic/Assets/Images/logo-white.png';
            $moved = move_uploaded_file($this->logo->getPathname(), $targetPath);

            if (!$moved) {
                echo 'Error';
            } else {
                echo 'Ok';
            }

            @chmod($targetPath, 0666 & ~umask());
        }

        if (!empty($this->favicon)) {
            $targetPath = './Dinamic/Assets/Images/favicon.ico';
            $moved = move_uploaded_file($this->favicon->getPathname(), $targetPath);

            if (!$moved) {
                echo 'Error';
            } else {
                echo 'Ok';
            }

            @chmod($targetPath, 0666 & ~umask());
        }

        if (!empty($this->login)) {
            $targetPath = './Dinamic/Assets/Images/horizontal-logo.png';
            $moved = move_uploaded_file($this->login->getPathname(), $targetPath);

            if (!$moved) {
                echo 'Error';
            } else {
                echo 'Ok';
            }

            @chmod($targetPath, 0666 & ~umask());
        }
    }
}
