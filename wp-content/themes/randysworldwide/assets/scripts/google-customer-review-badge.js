window.renderBadge = function() {
    var ratingBadgeContainer = document.createElement("div");
    document.body.appendChild(ratingBadgeContainer);
    window.gapi.load('ratingbadge', function() {
        window.gapi.ratingbadge.render(
            ratingBadgeContainer, {
                "merchant_id": 8883434,
                "position": "BOTTOM_LEFT"
        });
    });
};
