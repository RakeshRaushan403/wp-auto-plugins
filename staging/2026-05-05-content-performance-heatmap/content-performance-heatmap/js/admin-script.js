jQuery(document).ready(function($) {
    $('#cph-load-heatmap').on('click', function() {
        var button = $(this);
        button.prop('disabled', true).text('Loading...');
        
        $.ajax({
            url: cphAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cph_get_heatmap_data',
                nonce: cphAdmin.nonce,
                post_id: cphAdmin.postId
            },
            success: function(response) {
                if (response.success) {
                    renderHeatmap(response.data);
                    button.text('Refresh Heatmap');
                } else {
                    alert('Error loading heatmap data');
                    button.text('Load Heatmap');
                }
                button.prop('disabled', false);
            },
            error: function() {
                alert('AJAX error');
                button.prop('disabled', false).text('Load Heatmap');
            }
        });
    });
    
    function renderHeatmap(data) {
        var container = $('#cph-heatmap-visualization');
        container.empty();
        
        if (!data || data.length === 0) {
            container.html('<p>No engagement data available yet.</p>');
            return;
        }
        
        var html = '<table class="cph-heatmap-table">';
        html += '<thead><tr>';
        html += '<th>Paragraph</th>';
        html += '<th>Engagement</th>';
        html += '<th>Views</th>';
        html += '<th>Avg Time (s)</th>';
        html += '<th>Clicks</th>';
        html += '</tr></thead><tbody>';
        
        data.forEach(function(item) {
            var intensity = Math.round(item.intensity);
            var color = getHeatColor(intensity);
            
            html += '<tr>';
            html += '<td>' + (item.index + 1) + '</td>';
            html += '<td><div class="cph-heat-bar" style="background-color: ' + color + '; width: ' + intensity + '%;"></div></td>';
            html += '<td>' + item.views + '</td>';
            html += '<td>' + item.time + '</td>';
            html += '<td>' + item.clicks + '</td>';
            html += '</tr>';
        });
        
        html += '</tbody></table>';
        container.html(html);
    }
    
    function getHeatColor(intensity) {
        if (intensity >= 75) return '#d73027';
        if (intensity >= 50) return '#fc8d59';
        if (intensity >= 25) return '#fee08b';
        return '#d9ef8b';
    }
});