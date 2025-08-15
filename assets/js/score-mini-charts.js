/**
 * Score Mini Charts
 * Renders small line charts showing historical scores with target goals
 * 
 * @package ENNU_Life
 * @since 77.1.0
 */

(function($) {
    'use strict';

    // Initialize when DOM is ready
    $(document).ready(function() {
        console.log('Score mini charts initializing...');
        console.log('Found pillar cards:', $('.pillar-card').length);
        
        // Add a small delay to ensure all elements are loaded
        setTimeout(function() {
            try {
                // Initialize tooltip handlers first
                initTooltipHandlers();
                
                // Then render charts
                renderAllMiniCharts();
                renderWeightBMIChart();
                
                console.log('Charts rendered. Mini charts found:', $('.pillar-mini-chart').length);
            } catch (error) {
                console.error('Error initializing mini charts:', error);
            }
        }, 100);
    });

    /**
     * Render all mini charts on the page
     */
    function renderAllMiniCharts() {
        // Render pillar charts
        $('.pillar-card').each(function() {
            const $card = $(this);
            const pillar = $card.data('pillar');
            const currentScore = parseFloat($card.find('.pillar-score-value').text()) || 0;
            
            console.log(`Pillar ${pillar} has score: ${currentScore}`);
            
            // Skip if no score
            if (currentScore === 0) {
                console.log(`Skipping ${pillar} - no score`);
                return;
            }
            
            // Get historical scores from data attributes or generate mock data
            const historicalScores = getHistoricalScores(pillar, currentScore);
            
            // Calculate realistic target based on current performance
            const targetScore = calculateRealisticTarget(currentScore, pillar);
            
            // Create chart container if it doesn't exist, place above progress bar
            // BUT only select the FIRST progress bar (not biomarker progress bars)
            if (!$card.find('.pillar-mini-chart').length) {
                const $progress = $card.find('.pillar-progress-bar').first();
                if ($progress.length && !$progress.hasClass('biomarker-progress-bar')) {
                    $('<div class="pillar-mini-chart"></div>').insertBefore($progress);
                } else {
                    // Fallback - insert after score
                    const $score = $card.find('.pillar-card-score').first();
                    if ($score.length) {
                        $('<div class="pillar-mini-chart"></div>').insertAfter($score);
                    }
                }
            }
            
            // Render the chart with realistic target
            renderMiniChart($card.find('.pillar-mini-chart'), historicalScores, targetScore, pillar);
            
            // Skip adding target score display - let PHP handle all target calculations
            // This prevents the JavaScript from overriding PHP target values that change on refresh
            
            // Use PHP-calculated target if available, otherwise use JavaScript target
            const phpTargetElement = $card.find('.target-value-small');
            const phpTarget = phpTargetElement.length ? parseFloat(phpTargetElement.text()) : targetScore;
            
            // Progress bar calculation: 0% = score of 0, 100% = reached target
            // Current position on the bar is (currentScore / phpTarget) * 100
            const progressPercentage = (currentScore / phpTarget) * 100;
            
            // Update the progress bar data using PHP target
            $card.css('--progress', progressPercentage);
            $card.data('target-score', phpTarget);
        });
        
        // Render hero chart
        const $heroCard = $('.health-score-hero, .pillar-card.ennulife');
        if ($heroCard.length) {
            const currentScore = parseFloat($heroCard.find('.health-score-value, .pillar-score-value').text()) || 0;
            
            if (currentScore > 0) {
                const historicalScores = getHistoricalScores('overall', currentScore);
                const targetScore = calculateRealisticTarget(currentScore, 'overall');
                
                // Create chart container if it doesn't exist, place above progress bar
                if (!$heroCard.find('.hero-mini-chart, .pillar-mini-chart').length) {
                    const $progress = $heroCard.find('.health-score-progress, .pillar-progress-bar');
                    if ($progress.length) {
                        $('<div class="hero-mini-chart pillar-mini-chart"></div>').insertBefore($progress);
                    } else {
                        // Fallback - insert after score
                        const $score = $heroCard.find('.pillar-card-score');
                        if ($score.length) {
                            $('<div class="hero-mini-chart pillar-mini-chart"></div>').insertAfter($score);
                        } else {
                            $heroCard.append('<div class="hero-mini-chart pillar-mini-chart"></div>');
                        }
                    }
                }
                
                // Render the chart
                renderMiniChart($heroCard.find('.hero-mini-chart, .pillar-mini-chart'), historicalScores, targetScore, 'overall');
                
                // Skip adding target score display - let PHP handle all target calculations
                // This prevents the JavaScript from overriding PHP target values that change on refresh
                
                // Use PHP-calculated target if available, otherwise use JavaScript target
                const phpTargetElement = $heroCard.find('.target-value-small');
                const phpTarget = phpTargetElement.length ? parseFloat(phpTargetElement.text()) : targetScore;
                
                // Progress bar calculation: 0% = score of 0, 100% = reached target
                const progressPercentage = (currentScore / phpTarget) * 100;
                const clampedProgress = Math.max(0, Math.min(100, progressPercentage));
                
                // Update the progress bar data using PHP target
                $heroCard.css('--progress', clampedProgress);
                $heroCard.data('target-score', phpTarget);
            }
        }
    }

    /**
     * Get historical scores with dates (real data first, then realistic mock)
     */
    function getHistoricalScores(type, currentScore) {
        // 1. Check for real historical data from PHP/AJAX first
        if (window.ennuHistoricalData && window.ennuHistoricalData[type]) {
            const realData = window.ennuHistoricalData[type];
            if (realData && Array.isArray(realData) && realData.length > 0) {
                console.log(`Using real historical data for ${type}:`, realData);
                return realData.map(entry => ({
                    value: parseFloat(entry.value) || 0,
                    date: entry.date,
                    timestamp: entry.timestamp
                }));
            }
        }
        
        // 2. Check for data attributes (legacy)
        const $container = $(`[data-${type}-history]`);
        if ($container.length) {
            const history = $container.data(`${type}-history`);
            if (history && Array.isArray(history) && history.length > 0) {
                console.log(`Using data attribute history for ${type}:`, history);
                return history;
            }
        }
        
        // 3. Generate realistic mock historical data with natural variation
        console.log(`Generating realistic mock data for ${type}`);
        const scores = [];
        const today = new Date();
        
        // Always generate historical data for visualization
        if (currentScore > 0) {
            // Previous score 2 (2 months ago) - natural variation around current
            const date2 = new Date(today);
            date2.setMonth(date2.getMonth() - 2);
            const variance2 = (Math.random() - 0.5) * 1.6; // ±0.8 point variance
            const score2 = Math.max(0, Math.min(10, currentScore + variance2));
            scores.push({
                value: parseFloat(score2.toFixed(1)),
                date: formatDate(date2)
            });
            
            // Previous score 1 (1 month ago) - closer to current
            const date1 = new Date(today);
            date1.setMonth(date1.getMonth() - 1);
            const variance1 = (Math.random() - 0.5) * 1.0; // ±0.5 point variance
            const score1 = Math.max(0, Math.min(10, currentScore + variance1));
            scores.push({
                value: parseFloat(score1.toFixed(1)),
                date: formatDate(date1)
            });
            
            // Current score
            scores.push({
                value: currentScore,
                date: formatDate(today)
            });
        } else {
            // Even if no current score, create minimal data for visualization
            console.log('No current score for', type, '- creating placeholder data');
            scores.push({value: 0, date: formatDate(new Date())});
        }
        
        return scores;
    }
    
    /**
     * Calculate realistic target score based on current performance
     */
    function calculateRealisticTarget(currentScore, type) {
        // Base improvement potential on current score
        // Lower scores have more room for improvement
        let improvementPotential;
        
        if (currentScore < 5) {
            // Poor scores can improve by 20-30%
            improvementPotential = 2.0 + (Math.random() * 1.0);
        } else if (currentScore < 7) {
            // Average scores can improve by 15-20%
            improvementPotential = 1.0 + (Math.random() * 0.5);
        } else if (currentScore < 8.5) {
            // Good scores can improve by 10-15%
            improvementPotential = 0.5 + (Math.random() * 0.5);
        } else {
            // Excellent scores can improve by 5-10%
            improvementPotential = 0.2 + (Math.random() * 0.3);
        }
        
        // Calculate target with realistic 3-month goal
        const target = currentScore + improvementPotential;
        
        // Cap at realistic maximum (9.5 for pillars, 9.0 for overall)
        const maxTarget = type === 'overall' ? 9.0 : 9.5;
        
        return Math.min(maxTarget, parseFloat(target.toFixed(1)));
    }
    
    /**
     * Format date for display
     */
    function formatDate(date) {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return months[date.getMonth()] + ' ' + date.getDate();
    }

    /**
     * Render a mini line chart using SVG
     */
    function renderMiniChart($container, scores, targetScore, type, extraData) {
        if (!scores || !scores.length) {
            console.log('No scores to render for', type);
            return;
        }
        
        const width = $container.width() || $container.parent().width() || 280;
        const height = $container.height() || 50;
        const leftMargin = 50; // First dot positioned 50px from left
        const rightMargin = 20; // Target dot positioned 20px from right
        console.log('Chart container size:', width, 'x', height, 'for type:', type);
        
        // Calculate chart dimensions with specific margins
        const chartWidth = width - leftMargin - rightMargin;
        const chartHeight = height - 10; // Top/bottom padding
        
        // Prepare data points with target
        const dataPoints = [...scores];
        console.log(`Initial data points for ${type}:`, dataPoints);
        
        // For weight charts, limit to max 4 historical points (target will be 5th)
        if (type === 'weight' && dataPoints.length > 4) {
            dataPoints.splice(0, dataPoints.length - 4); // Keep only last 4
            console.log(`Weight data limited to 4 points:`, dataPoints);
        }
        
        // Ensure we have at least 2 data points for visualization (but respect 5-point limit)
        const maxHistoricalPoints = type === 'weight' ? 4 : 3; // Weight charts: 4 historical + 1 target = 5 max
        while (dataPoints.length < 2 && dataPoints.length < maxHistoricalPoints) {
            // Add placeholder points if needed
            if (dataPoints.length > 0 && dataPoints[0]) {
                // Add a point slightly higher than first point
                const firstVal = typeof dataPoints[0] === 'object' ? dataPoints[0].value : dataPoints[0];
                const firstPoint = dataPoints[0];
                
                // Calculate timestamp for historical point (1 month before first point)
                let historicalTimestamp;
                if (firstPoint && firstPoint.timestamp) {
                    historicalTimestamp = firstPoint.timestamp - (30 * 24 * 60 * 60); // 30 days earlier
                } else {
                    const monthAgo = new Date();
                    monthAgo.setMonth(monthAgo.getMonth() - 1);
                    historicalTimestamp = Math.floor(monthAgo.getTime() / 1000);
                }
                
                dataPoints.unshift({
                    value: firstVal + (type === 'weight' ? 2 : 0.2),
                    date: 'Historical',
                    timestamp: historicalTimestamp
                });
            } else {
                dataPoints.unshift(null);
            }
        }
        console.log(`After padding, data points for ${type}:`, dataPoints);
        
        // Add target as final point with future date
        const targetDate = new Date();
        targetDate.setMonth(targetDate.getMonth() + 3); // 3 months from now
        const targetPoint = {
            value: targetScore,
            date: formatDate(targetDate),
            timestamp: Math.floor(targetDate.getTime() / 1000), // Add timestamp for proper positioning
            isTarget: true
        };
        
        // Add BMI info if this is a weight chart
        if (type === 'weight' && extraData && extraData.targetBMI) {
            targetPoint.bmi = extraData.targetBMI;
        }
        
        dataPoints.push(targetPoint);
        
        // Final check: ensure total points don't exceed 5 for weight charts
        if (type === 'weight' && dataPoints.length > 5) {
            // Remove oldest historical points, keep target as last
            const target = dataPoints.pop(); // Remove target temporarily
            while (dataPoints.length > 4) {
                dataPoints.shift(); // Remove oldest
            }
            dataPoints.push(target); // Add target back as last point
            console.log(`Weight chart final limit applied - ${dataPoints.length} total points:`, dataPoints);
        }
        
        // Create SVG with proper viewBox that fits the container
        const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        svg.setAttribute("width", width);
        svg.setAttribute("height", height);
        svg.setAttribute("viewBox", `0 0 ${width} ${height}`);
        
        // Set chart color for weight charts explicitly
        if (type === 'weight') {
            svg.style.setProperty('--chart-color', '#6b7280');
        }
        
        // Calculate scale based on type with improved visual accuracy
        let minY, maxY;
        
        if (type === 'weight') {
            // For weight, use tighter range for better visual resolution
            const allValues = scores.filter(s => s).map(s => s.value).concat([targetScore]);
            const dataMin = Math.min(...allValues);
            const dataMax = Math.max(...allValues);
            const dataRange = dataMax - dataMin;
            
            // Use 20% padding above/below data range, minimum 5 lbs
            const padding = Math.max(5, dataRange * 0.2);
            minY = dataMin - padding;
            maxY = dataMax + padding;
        } else if (type === 'bmi') {
            // For BMI, use tighter range focused on data
            const allValues = scores.filter(s => s).map(s => s.value).concat([targetScore]);
            const dataMin = Math.min(...allValues);
            const dataMax = Math.max(...allValues);
            const dataRange = dataMax - dataMin;
            
            // Use 15% padding, but keep within reasonable BMI bounds
            const padding = Math.max(1, dataRange * 0.15);
            minY = Math.max(15, dataMin - padding);
            maxY = Math.min(50, dataMax + padding);
        } else {
            // For score charts, use dynamic range based on actual data
            const allValues = scores.filter(s => s).map(s => s.value).concat([targetScore]);
            const dataMin = Math.min(...allValues);
            const dataMax = Math.max(...allValues);
            const dataRange = dataMax - dataMin;
            
            // If data range is small (< 2 points), use tighter scale
            if (dataRange < 2) {
                const padding = Math.max(0.5, dataRange * 0.3);
                minY = Math.max(0, dataMin - padding);
                maxY = Math.min(10, dataMax + padding);
            } else {
                // For larger ranges, use 0-10 scale to show full context
                minY = 0;
                maxY = 10;
            }
        }
        
        // Calculate positioning with specific margins: first dot at 50px, target at 20px from right
        let getX = function(item, index) {
            return leftMargin + (index / (dataPoints.length - 1)) * chartWidth;
        };
        
        // Helper function to get Y coordinate
        function getY(item) {
            if (item === null || item === undefined) return null;
            const value = typeof item === 'object' ? item.value : item;
            if (value === null || value === undefined) return null;
            return 5 + chartHeight - ((value - minY) / (maxY - minY)) * chartHeight;
        }
        
        // No special gradient needed for EnnuLife - using greyscale defined in CSS
        
        // Draw target line (horizontal dashed line at target score) - only for score charts
        if (type !== 'weight' && type !== 'bmi') {
            const targetLine = document.createElementNS("http://www.w3.org/2000/svg", "line");
            targetLine.setAttribute("x1", leftMargin);
            targetLine.setAttribute("y1", getY({value: targetScore}));
            targetLine.setAttribute("x2", width - rightMargin);
            targetLine.setAttribute("y2", getY({value: targetScore}));
            targetLine.setAttribute("class", "chart-line-goal");
            svg.appendChild(targetLine);
        }
        
        // Determine stroke color based on type first (moved up to fix initialization issue)
        let strokeColor = '#6366f1'; // Default
        if (type === 'weight' || type === 'bmi') {
            strokeColor = '#6b7280'; // Grey for weight/BMI
        } else if (type === 'Mind') {
            strokeColor = '#5c6bc0';
        } else if (type === 'Body') {
            strokeColor = '#ff6b6b';
        } else if (type === 'Lifestyle') {
            strokeColor = '#00bcd4';
        } else if (type === 'Aesthetics') {
            strokeColor = '#f59e0b';
        } else if (type === 'EnnuLife' || type === 'overall') {
            strokeColor = '#6b7280';
        }
        
        // Add tapered trailing line before first point to show historical context
        const firstValidPoint = dataPoints.find(p => p !== null);
        if (firstValidPoint) {
            const firstPointIndex = dataPoints.findIndex(p => p !== null);
            const firstX = getX(firstValidPoint, firstPointIndex);
            const firstY = getY(firstValidPoint);
            
            if (firstY !== null && firstX !== null) {
                // Create a gradient for the trailing line from left edge to first dot
                const gradientId = `trail-gradient-${Math.random().toString(36).substr(2, 9)}`;
                const gradient = document.createElementNS("http://www.w3.org/2000/svg", "linearGradient");
                gradient.setAttribute("id", gradientId);
                gradient.setAttribute("x1", "0%");
                gradient.setAttribute("y1", "0%");
                gradient.setAttribute("x2", "100%");
                gradient.setAttribute("y2", "0%");
                
                // Gradient stops for smooth taper and fade effect (fade in from left)
                const stop1 = document.createElementNS("http://www.w3.org/2000/svg", "stop");
                stop1.setAttribute("offset", "0%");
                stop1.setAttribute("stop-color", strokeColor);
                stop1.setAttribute("stop-opacity", "0");
                
                const stop2 = document.createElementNS("http://www.w3.org/2000/svg", "stop");
                stop2.setAttribute("offset", "40%");
                stop2.setAttribute("stop-color", strokeColor);
                stop2.setAttribute("stop-opacity", "0.2");
                
                const stop3 = document.createElementNS("http://www.w3.org/2000/svg", "stop");
                stop3.setAttribute("offset", "80%");
                stop3.setAttribute("stop-color", strokeColor);
                stop3.setAttribute("stop-opacity", "0.6");
                
                const stop4 = document.createElementNS("http://www.w3.org/2000/svg", "stop");
                stop4.setAttribute("offset", "100%");
                stop4.setAttribute("stop-color", strokeColor);
                stop4.setAttribute("stop-opacity", "1.0");
                
                gradient.appendChild(stop1);
                gradient.appendChild(stop2);
                gradient.appendChild(stop3);
                gradient.appendChild(stop4);
                
                // Add gradient to defs
                let defs = svg.querySelector("defs");
                if (!defs) {
                    defs = document.createElementNS("http://www.w3.org/2000/svg", "defs");
                    svg.insertBefore(defs, svg.firstChild);
                }
                defs.appendChild(gradient);
                
                // Draw trailing line from left edge (5px) to first dot with slight Y variation
                const trailStartX = 5; // Start from left edge
                const trailStartY = firstY + (Math.random() - 0.5) * 4; // Slight natural variation
                
                const trailPath = document.createElementNS("http://www.w3.org/2000/svg", "path");
                trailPath.setAttribute("d", `M ${trailStartX} ${trailStartY} L ${firstX} ${firstY}`);
                trailPath.setAttribute("stroke", `url(#${gradientId})`);
                trailPath.setAttribute("stroke-width", "2");
                trailPath.setAttribute("fill", "none");
                trailPath.setAttribute("stroke-linecap", "round");
                
                // Animate trailing line
                const trailLength = trailPath.getTotalLength();
                trailPath.style.strokeDasharray = trailLength + ' ' + trailLength;
                trailPath.style.strokeDashoffset = trailLength;
                
                svg.appendChild(trailPath);
                
                // Start trailing line animation immediately
                setTimeout(() => {
                    trailPath.style.transition = 'stroke-dashoffset 0.8s ease-out';
                    trailPath.style.strokeDashoffset = '0';
                }, 50);
            }
        }
        
        // Add future trailing line after target point
        const targetDataPoint = dataPoints[dataPoints.length - 1]; // Target is last point
        if (targetDataPoint && targetDataPoint.isTarget) {
            const targetY = getY(targetDataPoint);
            const targetXPos = getX(targetDataPoint, dataPoints.length - 1);
            
            if (targetY !== null) {
                // Create a gradient for the future trailing line
                const futureGradientId = `future-gradient-${Math.random().toString(36).substr(2, 9)}`;
                const futureGradient = document.createElementNS("http://www.w3.org/2000/svg", "linearGradient");
                futureGradient.setAttribute("id", futureGradientId);
                futureGradient.setAttribute("x1", "0%");
                futureGradient.setAttribute("y1", "0%");
                futureGradient.setAttribute("x2", "100%");
                futureGradient.setAttribute("y2", "0%");
                
                // Gradient stops - fade from visible to transparent (smaller than historical)
                const fStop1 = document.createElementNS("http://www.w3.org/2000/svg", "stop");
                fStop1.setAttribute("offset", "0%");
                fStop1.setAttribute("stop-color", "var(--chart-goal, #10b981)");
                fStop1.setAttribute("stop-opacity", "0.4");
                
                const fStop2 = document.createElementNS("http://www.w3.org/2000/svg", "stop");
                fStop2.setAttribute("offset", "50%");
                fStop2.setAttribute("stop-color", "var(--chart-goal, #10b981)");
                fStop2.setAttribute("stop-opacity", "0.2");
                
                const fStop3 = document.createElementNS("http://www.w3.org/2000/svg", "stop");
                fStop3.setAttribute("offset", "100%");
                fStop3.setAttribute("stop-color", "var(--chart-goal, #10b981)");
                fStop3.setAttribute("stop-opacity", "0");
                
                futureGradient.appendChild(fStop1);
                futureGradient.appendChild(fStop2);
                futureGradient.appendChild(fStop3);
                
                // Add gradient to defs
                let defs = svg.querySelector("defs");
                if (!defs) {
                    defs = document.createElementNS("http://www.w3.org/2000/svg", "defs");
                    svg.insertBefore(defs, svg.firstChild);
                }
                defs.appendChild(futureGradient);
                
                // Draw future trailing line (stays within bounds, fades at edge)
                const trailEndX = Math.min(targetXPos + 15, width - 2); // Don't go past edge
                const trailEndY = targetY + (Math.random() - 0.5) * 3; // Slight variation
                
                const futurePath = document.createElementNS("http://www.w3.org/2000/svg", "path");
                futurePath.setAttribute("d", `M ${targetXPos} ${targetY} Q ${targetXPos + 8} ${targetY} ${trailEndX} ${trailEndY}`);
                futurePath.setAttribute("stroke", `url(#${futureGradientId})`);
                futurePath.setAttribute("stroke-width", "1.5");
                futurePath.setAttribute("fill", "none");
                futurePath.setAttribute("stroke-linecap", "round");
                futurePath.setAttribute("stroke-dasharray", "3,3");
                svg.appendChild(futurePath);
            }
        }
        
        // strokeColor already defined above for trailing line gradient
        
        // Build path for all scores including target
        let pathData = "";
        let firstPoint = true;
        let validPoints = 0;
        
        dataPoints.forEach((point, index) => {
            if (point !== null) {
                // Use the getX function to determine positioning
                const x = getX(point, index);
                const y = getY(point);
                
                if (y !== null && !isNaN(y) && x !== null && !isNaN(x)) {
                    validPoints++;
                    if (firstPoint) {
                        pathData += `M ${x} ${y}`;
                        firstPoint = false;
                    } else {
                        // Check if this is target point
                        if (point.isTarget) {
                            // Don't add to main path, will draw dashed line separately
                            const prevPoint = dataPoints[index - 1];
                            const prevX = prevPoint ? getX(prevPoint, index - 1) : null;
                            const prevY = getY(prevPoint);
                            if (prevY !== null && prevX !== null) {
                                // Create a dashed line to target
                                const dashPath = document.createElementNS("http://www.w3.org/2000/svg", "path");
                                dashPath.setAttribute("d", `M ${prevX} ${prevY} L ${x} ${y}`);
                                dashPath.setAttribute("class", "chart-line-target");
                                dashPath.setAttribute("stroke", strokeColor || '#6b7280');
                                dashPath.setAttribute("stroke-width", "2");
                                dashPath.setAttribute("stroke-dasharray", "5,5");
                                dashPath.setAttribute("fill", "none");
                                dashPath.setAttribute("opacity", "0.5");
                                
                                // Animate dashed line to target
                                const dashLength = dashPath.getTotalLength();
                                dashPath.style.strokeDasharray = dashLength + ' ' + dashLength;
                                dashPath.style.strokeDashoffset = dashLength;
                                
                                svg.appendChild(dashPath);
                                
                                // Animate target line after main line completes
                                setTimeout(() => {
                                    dashPath.style.transition = 'stroke-dashoffset 0.8s ease-out';
                                    dashPath.style.strokeDashoffset = '0';
                                    dashPath.style.strokeDasharray = "5,5"; // Reset to dashed pattern
                                }, 1300); // After main line animation (1.5s) - 200ms earlier
                            }
                        } else {
                            // Regular line segment
                            pathData += ` L ${x} ${y}`;
                        }
                    }
                }
            }
        });
        
        // Draw the line if we have data
        console.log(`Chart ${type}: ${validPoints} valid points, pathData length: ${pathData.length}`);
        if (pathData && pathData.length > 0 && validPoints >= 2) {
            console.log('Drawing animated path:', pathData);
            const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
            path.setAttribute("d", pathData);
            path.setAttribute("class", "chart-line-history");
            path.setAttribute("fill", "none");
            path.setAttribute("stroke", strokeColor);
            path.setAttribute("stroke-width", "2");
            
            // Add animation: line draws from left to right
            const pathLength = path.getTotalLength();
            path.style.strokeDasharray = pathLength + ' ' + pathLength;
            path.style.strokeDashoffset = pathLength;
            
            svg.appendChild(path);
            
            // Animate the line drawing
            setTimeout(() => {
                path.style.transition = 'stroke-dashoffset 1.5s ease-in-out';
                path.style.strokeDashoffset = '0';
            }, 100);
        } else {
            console.log(`No path drawn for ${type}: validPoints=${validPoints}, pathData="${pathData}"`);
        }
        
        // Draw dots for all scores including target with tooltips - animated to appear sequentially
        dataPoints.forEach((point, index) => {
            if (point !== null) {
                // Use the getX function to determine positioning
                const x = getX(point, index);
                const y = getY(point);
                
                if (y !== null && x !== null && !isNaN(x)) {
                    const circle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
                    circle.setAttribute("cx", x);
                    circle.setAttribute("cy", y);
                    
                    // Format value based on type
                    let formattedValue;
                    if (type === 'weight') {
                        formattedValue = point.value.toFixed(0) + ' lbs';
                        // Add BMI to tooltip if available
                        if (point.bmi) {
                            formattedValue += '<br>BMI: ' + point.bmi.toFixed(1);
                        }
                    } else if (type === 'bmi') {
                        formattedValue = 'BMI ' + point.value.toFixed(1);
                    } else {
                        formattedValue = point.value.toFixed(1);
                    }
                    
                    // Style based on whether it's current, historical, or target
                    if (point.isTarget) {
                        circle.setAttribute("r", "5");
                        circle.setAttribute("class", "chart-dot chart-dot-target");
                        circle.setAttribute("style", "fill: var(--chart-goal, #10b981); stroke: white; stroke-width: 3;");
                        circle.setAttribute("data-score", formattedValue);
                        circle.setAttribute("data-date", point.date);
                        circle.setAttribute("data-label", "Target Goal");
                    } else if (index === 2) { // Current score
                        circle.setAttribute("r", "5.5");
                        circle.setAttribute("class", "chart-dot chart-dot-current");
                        circle.setAttribute("data-score", formattedValue);
                        circle.setAttribute("data-date", point.date);
                        circle.setAttribute("data-label", "Current");
                    } else {
                        circle.setAttribute("r", "4");
                        circle.setAttribute("class", "chart-dot");
                        circle.setAttribute("data-score", formattedValue);
                        circle.setAttribute("data-date", point.date);
                        circle.setAttribute("data-label", index === 0 ? "2 months ago" : "1 month ago");
                    }
                    
                    // Start dots invisible and animate them in sequentially
                    circle.style.opacity = '0';
                    circle.style.transform = 'scale(0)';
                    circle.style.transformOrigin = 'center';
                    circle.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
                    
                    svg.appendChild(circle);
                    
                    // Animate dots to appear sequentially (trailing line starts at 0.2s, dots follow line progression)
                    const animationDelay = 200 + (index * 300); // Start after trailing line, then 300ms between dots
                    setTimeout(() => {
                        circle.style.opacity = '1';
                        circle.style.transform = 'scale(1)';
                    }, animationDelay);
                }
            }
        });
        
        // Clear container and add SVG
        console.log('Appending SVG to container:', $container.length, 'elements');
        console.log('SVG has', svg.childNodes.length, 'child nodes');
        $container.empty().append(svg);
        
        // Add tooltip handlers
        attachTooltipHandlers($container);
    }

    /**
     * Initialize global tooltip handlers (only once)
     */
    function initTooltipHandlers() {
        // Only initialize once
        if (window.ENNUTooltipsInitialized) return;
        window.ENNUTooltipsInitialized = true;
        
        // Create tooltip element if it doesn't exist
        if (!$('#chart-tooltip').length) {
            $('body').append('<div id="chart-tooltip" class="chart-tooltip"></div>');
        }
        
        const $tooltip = $('#chart-tooltip');
        let tooltipTimeout;
        
        // Store current dot to prevent flicker
        let currentDot = null;
        
        // Use event delegation on document for all chart dots
        $(document).on('mouseenter', '.chart-dot', function(e) {
            // Clear any hide timeout
            clearTimeout(tooltipTimeout);
            
            // Skip if same dot
            if (currentDot === this) return;
            currentDot = this;
            
            const $dot = $(this);
            const score = $dot.attr('data-score');
            const label = $dot.attr('data-label');
            const date = $dot.attr('data-date');
            
            if (score) {
                // Update tooltip content with score and date
                let tooltipContent = `<div class="tooltip-score">${score}</div>`;
                if (date) {
                    tooltipContent += `<div class="tooltip-date">${date}</div>`;
                }
                if (label) {
                    tooltipContent += `<div class="tooltip-label">${label}</div>`;
                }
                
                $tooltip.html(tooltipContent);
                
                // Show tooltip for measurement
                $tooltip.css({
                    display: 'block',
                    opacity: 0
                });
                
                // Get dot position using getBoundingClientRect
                const dotRect = this.getBoundingClientRect();
                const tooltipWidth = $tooltip.outerWidth();
                const tooltipHeight = $tooltip.outerHeight();
                
                // Calculate centered position above dot
                let left = dotRect.left + (dotRect.width / 2) - (tooltipWidth / 2);
                let top = dotRect.top - tooltipHeight - 10;
                
                // Viewport boundary checks
                if (left < 5) {
                    left = 5;
                } else if (left + tooltipWidth > window.innerWidth - 5) {
                    left = window.innerWidth - tooltipWidth - 5;
                }
                
                if (top < 5) {
                    // Show below if no space above
                    top = dotRect.bottom + 10;
                }
                
                // Position and show tooltip
                $tooltip.css({
                    left: Math.round(left) + 'px',
                    top: Math.round(top) + 'px',
                    opacity: 1
                });
            }
        });
        
        // Hide tooltip on mouse leave
        $(document).on('mouseleave', '.chart-dot', function() {
            currentDot = null;
            clearTimeout(tooltipTimeout);
            // Fade out then hide
            $tooltip.css('opacity', 0);
            setTimeout(() => {
                $tooltip.hide();
            }, 150);
        });
        
        // Hide tooltip immediately when scrolling
        $(window).on('scroll', function() {
            clearTimeout(tooltipTimeout);
            currentDot = null;
            $tooltip.hide();
        });
    }
    /**
     * Attach tooltip event handlers to chart dots (deprecated - use initTooltipHandlers)
     */
    function attachTooltipHandlers($container) {
        // Just initialize global handlers, no need for container-specific ones
        initTooltipHandlers();
    }

    /**
     * Render weight/BMI chart using the unified mini chart system
     */
    function renderWeightBMIChart() {
        const $container = $('.weight-bmi-mini-chart-container');
        if (!$container.length) return;
        
        const currentWeight = parseFloat($container.data('current-weight'));
        const currentBMI = parseFloat($container.data('current-bmi'));
        const targetWeight = parseFloat($container.data('target-weight')) || 0;
        const targetBMI = parseFloat($container.data('target-bmi')) || 0;
        
        if (!currentWeight) return;
        
        // Create chart container using the same approach as pillar charts
        if (!$container.find('.pillar-mini-chart').length) {
            // Create single weight chart (BMI will be shown in tooltips)
            $container.append('<div class="chart-label">Weight & BMI History</div>');
            $container.append('<div class="pillar-mini-chart weight-chart"></div>');
        }
        
        // Try to get real weight history from the data attribute or window variable
        let weightHistory = [];
        
        // Check if weight history was passed from PHP
        if (window.weightHistoryData && Array.isArray(window.weightHistoryData) && window.weightHistoryData.length > 0) {
            // Use real historical data, but limit to 5 most recent entries
            let sortedData = [...window.weightHistoryData];
            
            // Sort by timestamp/date to get chronological order
            sortedData.sort((a, b) => {
                const timeA = a.timestamp || new Date(a.date).getTime() / 1000;
                const timeB = b.timestamp || new Date(b.date).getTime() / 1000;
                return timeA - timeB;
            });
            
            // Take only the 4 most recent entries (leaving room for target as 5th point)
            const recentData = sortedData.slice(-4);
            
            weightHistory = recentData.map(entry => ({
                value: parseFloat(entry.weight) || 0,
                bmi: parseFloat(entry.bmi) || 0,
                date: entry.date || '',
                timestamp: entry.timestamp
            }));
            console.log(`Using ${weightHistory.length} most recent weight entries (limited from ${window.weightHistoryData.length}, target will be 5th point):`, weightHistory);
        } else {
            // Create realistic weight history for visualization (limited to 3-4 points)
            const today = new Date();
            weightHistory = [];
            
            // Create 3-4 historical points (target will be 5th point)
            const numPoints = Math.min(4, 3 + Math.floor(Math.random() * 2)); // 3 or 4 historical points
            
            for (let i = numPoints - 1; i >= 0; i--) {
                const date = new Date(today);
                date.setMonth(date.getMonth() - i);
                
                // Natural weight fluctuation (±2-4 lbs)
                const variance = i > 0 ? (Math.random() - 0.5) * 4 : 0;
                weightHistory.push({
                    value: currentWeight + variance,
                    bmi: currentBMI ? currentBMI + (variance * 0.15) : 0,
                    date: formatDate(date),
                    timestamp: Math.floor(date.getTime() / 1000)
                });
            }
            console.log(`Created realistic weight history (${numPoints} historical points, target will be 5th):`, weightHistory);
        }
        
        // Calculate realistic targets if not set
        const finalTargetWeight = targetWeight || (currentWeight * 0.95); // 5% weight loss
        const finalTargetBMI = targetBMI || (currentBMI - 1.5);
        
        // Add target with BMI info
        const targetData = {
            targetWeight: finalTargetWeight,
            targetBMI: finalTargetBMI
        };
        
        // Render weight chart with BMI data using the unified system
        renderMiniChart($container.find('.weight-chart'), weightHistory, finalTargetWeight, 'weight', targetData);
    }
    
    // Expose functions for external use
    window.ENNUMiniCharts = {
        render: renderAllMiniCharts,
        renderChart: renderMiniChart,
        renderWeightBMI: renderWeightBMIChart
    };

})(jQuery);