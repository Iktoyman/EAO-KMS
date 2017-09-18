
//initialization options for the progress bar
var progress = $("#progress").shieldProgressBar({
    min: 0,
    max: 100,
    value: 20,
    layout: "circular",
    layoutOptions: {
        circular: {
            borderColor: "black",
            width: 17,
            borderWidth: 3,
            color: "#FFC400",
            backgroundColor: "transparent"
        }
    },
    text: {
        enabled: true,
        template: '<span style="font-size:47px; color: #FFC400">{0:n1}%</span>'
    },
}).swidget();


function resetActive(event, percent, step) {
    progress.value(percent);

    $(".progress-bar").css("width", percent + "%").attr("aria-valuenow", percent);
    $(".progress-completed").text(percent + "%");

    $("div").each(function () {
        if ($(this).hasClass("activestep")) {
            $(this).removeClass("activestep");
        }
    });

    if (event.target.className == "col-md-2") {
        $(event.target).addClass("activestep");
    }
    else {
        $(event.target.parentNode).addClass("activestep");
    }

    hideSteps();
    showCurrentStepInfo(step);
}

function hideSteps() {
    $("div").each(function () {
        if ($(this).hasClass("activeStepInfo")) {
            $(this).removeClass("activeStepInfo");
            $(this).addClass("hiddenStepInfo");
        }
    });
}

function showCurrentStepInfo(step) {
    var id = "#" + step;
    $(id).addClass("activeStepInfo");
}