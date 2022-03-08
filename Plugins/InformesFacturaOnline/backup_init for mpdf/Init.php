<?php
/**
 * Copyright (C) 2019-2021 Carlos Garcia Gomez <carlos@facturascripts.com>
 */
namespace FacturaScripts\Plugins\InformesFacturaOnline;

use FacturaScripts\Dinamic\Lib\ExportManager;

/**
 * Composer autoload.
 */
require_once __DIR__ . '/vendor/autoload.php';

use FacturaScripts\Core\Base\InitClass;

class Init extends InitClass
{

    public function init(){
        ExportManager::addTool('main', 'AdminPlantillasPDF', 'pdf-templates', 'fas fa-cog');
    }

    public function update()
    {
        /// sets default config
        $defaults = [
            'bottommargin' => 20,
            'color1' => '#2770CA',
            'color2' => '#FFFFFF',
            'color3' => '#F1F1F1',
            'endalign' => 'justify',
            'endfontsize' => 10,
            'endtext' => '',
            'font' => 'DejaVuSans',
            'fontcolor' => '#000000',
            'fontsize' => 12,
            'footeralign' => 'center',
            'footerfontsize' => 10,
            'footertext' => '{PAGENO} / {nbpg}',
            'linecols' => 'descripcion,cantidad,pvpunitario,dtopor,pvptotal,iva,recargo,irpf',
            'linecolalignments' => 'left,right,right,right,right,right,right,right',
            'linecoltypes' => 'text,number2,number,percentage,number,percentage0,percentage1,percentage0',
            'linesheight' => 400,
            'logoalign' => 'right',
            'logosize' => 100,
            'orientation' => 'portrait',
            'size' => 'A4',
            'thankstext' => '',
            'thankstitle' => '',
            'template' => 'Template1',
            'titlefontsize' => 18,
            'topmargin' => 45
        ];

        $appSettings = $this->toolBox()->appSettings();
        foreach ($defaults as $key => $value) {
            $appSettings->get('plantillaspdf', $key, $value);
        }
        $appSettings->save();
    }
}
