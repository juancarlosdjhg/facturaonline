/*!
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

function widgetAutocompleteGetData(formId, formData, term) {
    var rawForm = $("form[id=" + formId + "]").serializeArray();
    $.each(rawForm, function (i, input) {
        formData[input.name] = input.value;
    });
    formData["action"] = "autocomplete";
    formData["term"] = term;
    return formData;
}

$(document).ready(function () {
    $(".widget-autocomplete").each(function () {
        var data = {
            field: $(this).attr("data-field"),
            fieldcode: $(this).attr("data-fieldcode"),
            fieldfilter: $(this).attr("data-fieldfilter"),
            fieldtitle: $(this).attr("data-fieldtitle"),
            source: $(this).attr("data-source"),
            strict: $(this).attr("data-strict"),
            modelData: []
        };
        var formId = $(this).closest("form").attr("id");
        $(this).autocomplete({
            source: function (request, response) {
                $.ajax({
                    method: "POST",
                    url: window.location.href,
                    data: widgetAutocompleteGetData(formId, data, request.term),
                    dataType: "json",
                    success: function (results) {
                        var values = [];
                        results.forEach((element) => {
                            values.push(element.key === null || element.key === element.value ? element : {key: element.key, value: element.key + " | " + element.value});
                            
                            if(element['model_data']) {
                                data.modelData.push(element['model_data']);
                            }
                        });

                        response(values);
                    },
                    error: function (msg) {
                        alert(msg.status + " " + msg.responseText);
                    }
                });
            },
            select: function (event, ui) {
                if (ui.item.key !== null) {
                    $("form[id=" + formId + "] input[name=" + data.field + "]").val(ui.item.value);
                    if(data.modelData.length >= 1) {
                        var model = data.modelData.find(item => Object.values(item).includes(ui.item.key));
                        if (model) {
                            Object.keys(model).forEach(i => {$(`form[id=${formId}] input[name=${i}]`).val(model[i]);});
                        } 
                        else {
                            data.modelData.forEach(item => Object.keys(item).forEach(i => {
                                switch(i){
                                    case "idproducto":
                                        $(`form[id="${formId}"] input[name="${i}"]`).val('');
                                        $(`form[id="${formId}"] input[name="${i}"]`).val(data.modelData[data.modelData.length-1].idproducto);
                                        break;
                                    case "descripcionproducto":
                                        $(`form[id="${formId}"] input[name="${i}"]`).val('');
                                        $(`form[id="${formId}"] input[name="${i}"]`).val(data.modelData[data.modelData.length-1].descripcionproducto);
                                        break;
                                    case "coste":
                                        $(`form[id="${formId}"] input[name="${i}"]`).val('');
                                        $(`form[id="${formId}"] input[name="${i}"]`).val(data.modelData[data.modelData.length-1].coste);
                                        break;
                                    case "codigoexterno":
                                        $(`form[id="${formId}"] input[name="${i}"]`).val('');
                                        $(`form[id="${formId}"] input[name="${i}"]`).val(data.modelData[data.modelData.length-1].codigoexterno);
                                        break;
                                    case "pvp":
                                        $(`form[id="${formId}"] input[name="${i}"]`).val('0');
                                        break;
                            }}));
                        }
                    }
                    var value = ui.item.value.split(" | ");
                    ui.item.value = value[0];
                }
            }
        });
    });
});