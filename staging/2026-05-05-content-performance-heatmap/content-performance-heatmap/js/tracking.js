jQuery(document).ready(function($) {
    var paragraphData = {};
    var startTime = Date.now();
    var updateInterval = 5000;
    
    $('.cph-paragraph').each(function() {
        var index = $(this).data('paragraph');
        paragraphData[index] = {
            scrollDepth: 0,
            timeSpent: 0,
            clicks: 0,
            inView: false,
            entryTime: 0
        };
    });
    
    function checkVisibility() {
        var scrollTop = $(window).scrollTop();
        var windowHeight = $(window).height();
        var currentTime = Date.now();
        
        $('.cph-paragraph').each(function() {
            var index = $(this).data('paragraph');
            var offset = $(this).offset().top;
            var height = $(this).height();
            
            if (scrollTop + windowHeight > offset && scrollTop < offset + height) {
                var visiblePercent = Math.min(100, Math.max(0, 
                    ((scrollTop + windowHeight - offset) / height) * 100
                ));
                
                if (!paragraphData[index].inView) {
                    paragraphData[index].inView = true;
                    paragraphData[index].entryTime = currentTime;
                }
                
                paragraphData[index].scrollDepth = Math.max(
                    paragraphData[index].scrollDepth,
                    Math.round(visiblePercent)
                );
                
                paragraphData[index].timeSpent += (currentTime - paragraphData[index].entryTime) / 1000;
                paragraphData[index].entryTime = currentTime;
            } else {
                paragraphData[index].inView = false;
            }
        });
    }
    
    $('.cph-paragraph').on('click', 'a, button', function() {
        var index = $(this).closest('.cph-paragraph').data('paragraph');
        if (paragraphData[index]) {
            paragraphData[index].clicks++;
        }
    });
    
    $(window).on('scroll', checkVisibility);
    
    function sendTrackingData() {
        Object.keys(paragraphData).forEach(function(index) {
            var data = paragraphData[index];
            
            if (data.scrollDepth > 0 || data.timeSpent > 0 || data.clicks > 0) {
                $.ajax({
                    url: cphData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'cph_track_engagement',
                        nonce: cphData.nonce,
                        post_id: cphData.postId,
                        paragraph_index: index,
                        scroll_depth: data.scrollDepth,
                        time_spent: Math.round(data.timeSpent),
                        clicks: data.clicks
                    }
                });
            }
        });
    }
    
    setInterval(sendTrackingData, updateInterval);
    
    $(window).on('beforeunload', function() {
        sendTrackingData();
    });
    
    checkVisibility();
});