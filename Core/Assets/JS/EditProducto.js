
$(document).ready(function () {
    $(".calc-cost1").change(function () {
        var coste1 = parseFloat($(this).val());
        var margen1 = parseFloat($(this.form.margen1).val());
        if (margen1 > 0) {
            var resultado = parseFloat(coste1 * (100 + margen1) / 100).toFixed(2);
            $(this.form.pvp1).val(resultado);
        }
    });
    $(".calc-target-margin1").change(function () {
        var coste1 = parseFloat($(this.form.coste).val());
        var margen1 = parseFloat($(this).val());
        if (margen1 > 0) {
            var resultado = parseFloat(coste1 * (100 + margen1) / 100).toFixed(2);
            $(this.form.preciorecomendado1).val(resultado);
        }
    });
    $(".calc-price1").change(function () {
        var coste1 = parseFloat($(this.form.coste).val());
        var pvp1 = parseFloat($(this.form.pvp1).val());
        var resultado = parseFloat(((pvp1 - coste1) / coste1 ) * 100).toFixed(2);
        $(this.form.margen1).val(resultado);
    });
    $(".calc-cost2").change(function () {
        var coste2 = parseFloat($(this).val());
        var margen2 = parseFloat($(this.form.margen2).val());
        if (margen2 > 0) {
            var resultado = parseFloat(coste2 * (100 + margen2) / 100).toFixed(2)
            $(this.form.pvp2).val(resultado);
        }
    });
    $(".calc-target-margin2").change(function () {
        var coste2 = parseFloat($(this.form.coste).val());
        var margen2 = parseFloat($(this).val());
        if (margen2 > 0) {
            var resultado = parseFloat(coste2 * (100 + margen2) / 100).toFixed(2);
            $(this.form.preciorecomendado2).val(resultado);
        }
    });
    $(".calc-price2").change(function () {
        var coste2 = parseFloat($(this.form.coste).val());
        var pvp2 = parseFloat($(this.form.pvp2).val());
        var resultado = parseFloat(((pvp2 - coste2) / coste2 ) * 100).toFixed(2);
        $(this.form.margen2).val(resultado);
    });
    $(".calc-cost3").change(function () {
        var coste3 = parseFloat($(this).val());
        var margen3 = parseFloat($(this.form.margen3).val());
        if (margen3 > 0) {
            var resultado = parseFloat(coste3 * (100 + margen3) / 100).toFixed(2);
            $(this.form.pvp3).val(resultado);
        }
    });
    $(".calc-target-margin3").change(function () {
        var coste3 = parseFloat($(this.form.coste).val());
        var margen3 = parseFloat($(this).val());
        if (margen3 > 0) {
            var resultado = parseFloat(coste3 * (100 + margen3) / 100).toFixed(2);
            $(this.form.preciorecomendado3).val(resultado);
        }
    });
    $(".calc-price3").change(function () {
        var coste3 = parseFloat($(this.form.coste).val());
        var pvp3 = parseFloat($(this.form.pvp3).val());
        var resultado = parseFloat(((pvp3 - coste3) / coste3 ) * 100).toFixed(2);
        $(this.form.margen3).val(resultado);
    });
    $(".calc-cost4").change(function () {
        var coste4 = parseFloat($(this).val());
        var margen4 = parseFloat($(this.form.margen4).val());
        if (margen4 > 0) {
            var resultado = parseFloat(coste4 * (100 + margen4) / 100).toFixed(2);
            $(this.form.pvp4).val(resultado);
        }
    });
    $(".calc-target-margin4").change(function () {
        var coste4 = parseFloat($(this.form.coste).val());
        var margen4 = parseFloat($(this).val());
        if (margen4 > 0) {
            var resultado = parseFloat(coste4 * (100 + margen4) / 100).toFixed(2);
            $(this.form.preciorecomendado4).val(resultado);
        }
    });
    $(".calc-price4").change(function () {
        var coste4 = parseFloat($(this.form.coste).val());
        var pvp4 = parseFloat($(this.form.pvp4).val());
        var resultado = parseFloat(((pvp4 - coste4) / coste4 ) * 100).toFixed(2);
        $(this.form.margen4).val(resultado);
    });
    $(".calc-cost5").change(function () {
        var coste5 = parseFloat($(this).val());
        var margen5 = parseFloat($(this.form.margen5).val());
        if (margen5 > 0) {
            var resultado = parseFloat(coste5 * (100 + margen5) / 100).toFixed(2);
            $(this.form.pvp5).val(resultado);
        }
    });
    $(".calc-target-margin5").change(function () {
        var coste5 = parseFloat($(this.form.coste).val());
        var margen5 = parseFloat($(this).val());
        if (margen5 > 0) {
            var resultado = parseFloat(coste5 * (100 + margen5) / 100).toFixed(2);
            $(this.form.preciorecomendado5).val(resultado);
        }
    });
    $(".calc-price5").change(function () {
        var coste5 = parseFloat($(this.form.coste).val());
        var pvp5 = parseFloat($(this.form.pvp5).val());
        var resultado = parseFloat(((pvp5 - coste5) / coste5 ) * 100).toFixed(2);
        $(this.form.margen5).val(resultado);
    });
});