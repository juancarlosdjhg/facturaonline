/*!
 * This file is part of FacturaScripts
 * Copyright (C) 2020 Carlos Garcia Gomez <carlos@facturascripts.com>
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

$(document).ready(function () {
    $(".calc-cost1").change(function () {
        var coste1 = parseFloat($(this).val());
        var margen1 = parseFloat($(this.form.margen1).val());
        if (margen1 > 0) {
            $(this.form.pvp1).val(coste1 * (100 + margen1) / 100);
        }
    });
    $(".calc-target-margin1").change(function () {
        var coste1 = parseFloat($(this.form.coste).val());
        var margen1 = parseFloat($(this).val());
        if (margen1 > 0) {
            $(this.form.preciorecomendado1).val(coste1 * (100 + margen1) / 100);
        }
    });
    $(".calc-price1").change(function () {
        var coste1 = parseFloat($(this.form.coste).val());
        var pvp1 = parseFloat($(this.form.pvp1).val());
        $(this.form.margen1).val(parseFloat(((pvp1 - coste1) / coste1 ) * 100).toFixed(2));
    });
    $(".calc-cost2").change(function () {
        var coste2 = parseFloat($(this).val());
        var margen2 = parseFloat($(this.form.margen2).val());
        if (margen2 > 0) {
            $(this.form.pvp2).val(coste2 * (100 + margen2) / 100);
        }
    });
    $(".calc-target-margin2").change(function () {
        var coste2 = parseFloat($(this.form.coste).val());
        var margen2 = parseFloat($(this).val());
        if (margen2 > 0) {
            $(this.form.preciorecomendado2).val(coste2 * (100 + margen2) / 100);
        }
    });
    $(".calc-price2").change(function () {
        var coste2 = parseFloat($(this.form.coste).val());
        var pvp2 = parseFloat($(this.form.pvp2).val());
        $(this.form.margen2).val(parseFloat(((pvp2 - coste2) / coste2 ) * 100).toFixed(2));
    });
    $(".calc-cost3").change(function () {
        var coste3 = parseFloat($(this).val());
        var margen3 = parseFloat($(this.form.margen3).val());
        if (margen3 > 0) {
            $(this.form.pvp3).val(coste3 * (100 + margen3) / 100);
        }
    });
    $(".calc-target-margin3").change(function () {
        var coste3 = parseFloat($(this.form.coste).val());
        var margen3 = parseFloat($(this).val());
        if (margen3 > 0) {
            $(this.form.preciorecomendado3).val(coste3 * (100 + margen3) / 100);
        }
    });
    $(".calc-price3").change(function () {
        var coste3 = parseFloat($(this.form.coste).val());
        var pvp3 = parseFloat($(this.form.pvp3).val());
        $(this.form.margen3).val(parseFloat(((pvp3 - coste3) / coste3 ) * 100).toFixed(2));
    });
    $(".calc-cost4").change(function () {
        var coste4 = parseFloat($(this).val());
        var margen4 = parseFloat($(this.form.margen4).val());
        if (margen4 > 0) {
            $(this.form.pvp4).val(coste4 * (100 + margen4) / 100);
        }
    });
    $(".calc-target-margin4").change(function () {
        var coste4 = parseFloat($(this.form.coste).val());
        var margen4 = parseFloat($(this).val());
        if (margen4 > 0) {
            $(this.form.preciorecomendado4).val(coste4 * (100 + margen4) / 100);
        }
    });
    $(".calc-price4").change(function () {
        var coste4 = parseFloat($(this.form.coste).val());
        var pvp4 = parseFloat($(this.form.pvp4).val());
        $(this.form.margen4).val(parseFloat(((pvp4 - coste4) / coste4 ) * 100).toFixed(2));
    });
    $(".calc-cost5").change(function () {
        var coste5 = parseFloat($(this).val());
        var margen5 = parseFloat($(this.form.margen5).val());
        if (margen5 > 0) {
            $(this.form.pvp5).val(coste5 * (100 + margen5) / 100);
        }
    });
    $(".calc-target-margin5").change(function () {
        var coste5 = parseFloat($(this.form.coste).val());
        var margen5 = parseFloat($(this).val());
        if (margen5 > 0) {
            $(this.form.preciorecomendado5).val(coste5 * (100 + margen5) / 100);
        }
    });
    $(".calc-price5").change(function () {
        var coste5 = parseFloat($(this.form.coste).val());
        var pvp5 = parseFloat($(this.form.pvp5).val());
        $(this.form.margen5).val(parseFloat(((pvp5 - coste5) / coste5 ) * 100).toFixed(2));
    });
});