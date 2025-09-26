<?php
require_once '../includes/auth_check.php';
require_once '../includes/db_connection.php';
include '../includes/header_private.php';

try {
    $stmt = $pdo->query("SELECT id, name, car_capacity, outstation, languages, price_per_km FROM drivers ORDER BY name ASC");
    $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Unable to load drivers at this time.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered Drivers</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Reset default styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Global styles */
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Container styles */
        .drivers-container {
            max-width: 80rem;
            margin: 2rem auto;
            padding: 2rem;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .drivers-container h2 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #7d5a9b;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        /* Alert styles */
        .alert-error {
            background-color: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
            padding: 0.75rem 1rem;
            margin-bottom: 1.5rem;
            border-radius: 4px;
            font-size: 0.9rem;
            text-align: center;
        }

        /* Cost calculator section */
        .cost-calc-section {
            margin-top: 1.5rem;
            background: #f8fafc;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .cost-calc-section h3 {
            font-size: 1.4rem;
            font-weight: 600;
            color: #7d5a9b;
            margin-bottom: 1.25rem;
            text-align: center;
        }

        .cost-calc-fields {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            gap: 1rem;
        }

        .field-wrap {
            position: relative;
            width: 100%;
            max-width: 400px;
            margin-bottom: 1rem;
        }

        .field-wrap input {
            width: 100%;
            padding: 0.75rem;
            font-size: 1rem;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            background: #ffffff;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .field-wrap input:focus {
            outline: none;
            border-color: #7d5a9b;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .osm-suggestions {
            position: absolute;
            background: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            max-height: 200px;
            overflow-y: auto;
            width: 100%;
            top: 3rem;
            z-index: 1000;
            padding: 0;
            margin: 0;
            list-style: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .osm-suggestions li {
            padding: 0.75rem;
            cursor: pointer;
            font-size: 0.95rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.5rem;
        }

        .osm-suggestions li:hover {
            background: #f1f5f9;
        }

        .osm-suggestions .loc-meta {
            color: #6b7280;
            font-size: 0.85rem;
        }

        /* Button styles */
        .btn-small {
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 500;
            border-radius: 4px;
            background: #7d5a9b;
            color: #ffffff;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .btn-small:hover {
            background: #7d5a9b;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
        }

        /* Table styles */
        .table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        .drivers-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
            background: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .drivers-table th,
        .drivers-table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e5e7eb;
            text-align: center;
        }

        .drivers-table th {
            background: #f1f5f9;
            color: #7d5a9b;
            font-weight: 600;
        }

        .drivers-table tr:hover {
            background: #f8fafc;
        }

        .driver-link {
            color: #7d5a9b;
            text-decoration: none;
            font-weight: 500;
        }

        .driver-link:hover {
            text-decoration: underline;
            color: #7d5a9b;
        }

        .register-driver-bottom {
            margin-top: 2rem;
            display: flex;
            justify-content: flex-end;
        }

        /* Filter icon */
        .filter-icon-float {
            position: fixed;
            right: 1.5rem;
            bottom: 1.5rem;
            background: none;
            border: none;
            z-index: 1002;
            cursor: pointer;
        }

        .filter-icon-float img {
            width: 40px;
            height: 40px;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
            transition: filter 0.2s;
        }

        .filter-icon-float img:hover {
            filter: drop-shadow(0 4px 8px rgba(59, 130, 246, 0.2));
        }

        /* Filter panel */
        .filter-panel-bg {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            z-index: 920;
            display: none;
        }

        .filter-panel {
            position: fixed;
            right: 0;
            bottom: 0;
            width: 300px;
            max-width: 100vw;
            background: #ffffff;
            box-shadow: -4px 0 8px rgba(0, 0, 0, 0.1);
            border-left: 1px solid #d1d5db;
            z-index: 950;
            padding: 1.5rem;
            display: none;
            flex-direction: column;
            gap: 1rem;
            border-radius: 8px 0 0 0;
        }

        .filter-panel-header {
            font-size: 1.25rem;
            font-weight: 600;
            color: #7d5a9b;
            margin-bottom: 0.5rem;
        }

        .filter-panel-close {
            background: none;
            border: none;
            position: absolute;
            right: 1rem;
            top: 1rem;
            font-size: 1.5rem;
            color: #6b7280;
            cursor: pointer;
        }

        .filter-panel label {
            font-size: 0.9rem;
            font-weight: 500;
            color: #7d5a9b;
        }

        .filter-panel .check-group,
        .filter-panel .radio-group {
            margin-bottom: 1rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .filter-panel .check-group label,
        .filter-panel .radio-group label {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .filter-panel input[type="number"] {
            width: 80px;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .filter-panel select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .filter-panel-footer {
            margin-top: 1rem;
        }

        .filter-panel-footer .btn-small {
            width: 100%;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .drivers-container {
                margin: 1.5rem;
                padding: 1.5rem;
            }

            .drivers-container h2 {
                font-size: 1.5rem;
            }

            .cost-calc-section {
                padding: 1rem;
            }

            .field-wrap {
                max-width: 100%;
            }

            .filter-icon-float {
                right: 1rem;
                bottom: 1rem;
            }

            .filter-panel {
                width: 100%;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
    <section class="drivers-container" style="position: relative;">
        <h2>Registered Drivers</h2>

        <?php if (isset($error)): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="cost-calc-section">
            <h3>Calculate Total Cost</h3>
            <div class="cost-calc-fields">
                <div class="field-wrap">
                    <input type="text" id="pickup-input" placeholder="Enter pickup location (default: College)" autocomplete="off" />
                    <ul id="pickup-suggestions" class="osm-suggestions"></ul>
                </div>
                <div class="field-wrap">
                    <input type="text" id="destination-input" placeholder="Enter destination" autocomplete="off" />
                    <ul id="destination-suggestions" class="osm-suggestions"></ul>
                </div>
                <button id="calculate-cost-btn" class="btn-small">Calculate Cost</button>
            </div>
        </div>

        <button class="filter-icon-float" id="filterBtn" title="Show filters">
            <img src="../assets/images/Filter.png" alt="Filter" id="filterBtnImg"/>
        </button>

        <div class="filter-panel-bg" id="filterPanelBg"></div>
        <div class="filter-panel" id="filterPanel">
            <button class="filter-panel-close" id="filterPanelClose" title="Close">&times;</button>
            <div class="filter-panel-header">Filters</div>
            <div class="check-group">
                <label><input type="checkbox" class="lang-checkbox" value="hindi"/> Hindi</label>
                <label><input type="checkbox" class="lang-checkbox" value="english"/> English</label>
                <label><input type="checkbox" class="lang-checkbox" value="telugu"/> Telugu</label>
                <label><input type="checkbox" class="lang-checkbox" value="tamil"/> Tamil</label>
            </div>
            <div class="radio-group">
                <label>Outstation:</label>
                <label><input type="radio" name="outstation" class="outstation-radio" value="" checked/> All</label>
                <label><input type="radio" name="outstation" class="outstation-radio" value="Yes"/> Yes</label>
                <label><input type="radio" name="outstation" class="outstation-radio" value="No"/> No</label>
            </div>
            <div>
                <label>Car Capacity:</label>
                <input type="number" id="cap-min" min="1" placeholder="Min"/>
                <input type="number" id="cap-max" min="1" placeholder="Max"/>
            </div>
            <div>
                <label>Sort by Cost:</label>
                <select id="sort-cost">
                    <option value="">None</option>
                    <option value="asc">Low to High</option>
                    <option value="desc">High to Low</option>
                </select>
            </div>
            <div class="filter-panel-footer">
                <button class="btn-small" id="apply-filters-btn">Apply Filters</button>
            </div>
        </div>

        <?php if (!empty($drivers)): ?>
            <div class="table-wrap">
                <table class="drivers-table" id="drivers-table">
                    <thead>
                        <tr>
                            <th>Driver Name</th>
                            <th>Car Seating Capacity</th>
                            <th>Outstation</th>
                            <th>Languages Spoken</th>
                            <th>Cost (₹)</th>
                        </tr>
                    </thead>
                    <tbody id="drivers-tbody">
                        <?php foreach ($drivers as $driver): ?>
                            <tr data-id="<?= intval($driver['id']) ?>"
                                data-outstation="<?= htmlspecialchars($driver['outstation']) ?>"
                                data-languages="<?= strtolower(htmlspecialchars($driver['languages'])) ?>"
                                data-capacity="<?= intval($driver['car_capacity']) ?>"
                                data-name="<?= strtolower(htmlspecialchars($driver['name'])) ?>"
                                data-price="<?= isset($driver['price_per_km']) ? floatval($driver['price_per_km']) : 1 ?>"
                            >
                                <td>
                                    <a href="driver_profile.php?driver_id=<?= intval($driver['id']) ?>" class="driver-link">
                                        <?= htmlspecialchars($driver['name']) ?>
                                    </a>
                                </td>
                                <td><?= intval($driver['car_capacity']) ?></td>
                                <td><?= htmlspecialchars($driver['outstation']) ?></td>
                                <td><?= htmlspecialchars($driver['languages']) ?></td>
                                <td class="cost-cell">-</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: #6b7280;">No drivers registered yet.</p>
        <?php endif; ?>

        <div class="register-driver-bottom">
            <a href="register_driver.php" class="btn-small">+ Register Driver</a>
        </div>
    </section>

    <footer class="private-footer">
        <div class="container">
            <p>&copy; 2025 CCP Ride Sharing. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function setupOSMAutocomplete(inputId, suggestionId, onSelect) {
            const input = document.getElementById(inputId);
            const suggestionBox = document.getElementById(suggestionId);
            let selectedCoords = null;
            input.addEventListener('input', function() {
                selectedCoords = null;
                const val = input.value.trim();
                if (val.length < 3) { suggestionBox.innerHTML = ''; return; }
                fetch('https://photon.komoot.io/api/?q=' + encodeURIComponent(val) + '&limit=7')
                    .then(res => res.json())
                    .then(data => {
                        suggestionBox.innerHTML = '';
                        if (data.features) {
                            data.features.forEach(f => {
                                const li = document.createElement('li');
                                let meta = [];
                                if (f.properties.city && f.properties.city.toLowerCase() !== (f.properties.name || f.properties.label || '').toLowerCase()) meta.push(f.properties.city);
                                if (f.properties.state) meta.push(f.properties.state);
                                if (f.properties.country) meta.push(f.properties.country);
                                li.innerHTML = `<span>${f.properties.label || f.properties.name || ""}</span><span class="loc-meta">${meta.length ? meta.join(', ') : ""}</span>`;
                                li.tabIndex = 0;
                                li.addEventListener('mousedown', function(e) {
                                    input.value = (f.properties.label || f.properties.name || "") + (meta.length ? " (" + meta.join(", ") + ")" : "");
                                    selectedCoords = f.geometry.coordinates.reverse();
                                    suggestionBox.innerHTML = '';
                                    if (onSelect) onSelect(selectedCoords);
                                });
                                suggestionBox.appendChild(li);
                            });
                        }
                    });
            });
            input.addEventListener('blur', () => { setTimeout(() => suggestionBox.innerHTML = '', 130); });
            return () => selectedCoords;
        }

        let pickupCoords = null, destCoords = null;
        setupOSMAutocomplete('pickup-input', 'pickup-suggestions', coords => { pickupCoords = coords; });
        setupOSMAutocomplete('destination-input', 'destination-suggestions', coords => { destCoords = coords; });

        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) ** 2 + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon / 2) ** 2;
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }

        document.addEventListener('DOMContentLoaded', () => {
            const tbody = document.getElementById('drivers-tbody');
            let drivers = Array.from(tbody.querySelectorAll('tr')).map(row => {
                return {
                    element: row,
                    id: row.dataset.id,
                    name: row.dataset.name,
                    outstation: row.dataset.outstation === "Yes",
                    languages: row.dataset.languages ? row.dataset.languages.toLowerCase() : '',
                    pricePerKm: parseFloat(row.dataset.price) || 1,
                    capacity: parseInt(row.dataset.capacity) || 1,
                    cost: null
                };
            });

            function filterPanelVisible(show) {
                document.getElementById('filterPanel').style.display = show ? 'flex' : 'none';
                document.getElementById('filterPanelBg').style.display = show ? 'block' : 'none';
            }

            document.getElementById('filterBtn').onclick = () => filterPanelVisible(true);
            document.getElementById('filterPanelClose').onclick = () => filterPanelVisible(false);
            document.getElementById('filterPanelBg').onclick = () => filterPanelVisible(false);

            function updateTableRows() {
                const checkedLang = Array.from(document.querySelectorAll('.lang-checkbox:checked')).map(cb => cb.value.toLowerCase());
                const outstationVal = document.querySelector('input[name="outstation"]:checked').value;
                const capMin = parseInt(document.getElementById('cap-min').value) || 0;
                const capMax = parseInt(document.getElementById('cap-max').value) || 999;
                const sortCost = document.getElementById('sort-cost').value;

                let filtered = drivers.filter(d => {
                    // Language filter
                    if (checkedLang.length > 0) {
                        const driverLanguages = d.languages.split(',').map(lang => lang.trim().toLowerCase());
                        const hasMatchingLanguage = checkedLang.some(lang => 
                            driverLanguages.some(driverLang => driverLang.includes(lang))
                        );
                        if (!hasMatchingLanguage) return false;
                    }

                    // Outstation filter
                    if (outstationVal !== '') {
                        if (outstationVal === 'Yes' && !d.outstation) return false;
                        if (outstationVal === 'No' && d.outstation) return false;
                    }

                    // Capacity filter
                    if (d.capacity < capMin) return false;
                    if (capMax > 0 && d.capacity > capMax) return false;

                    return true;
                });

                // Sort by cost if specified
                if (sortCost === 'asc') {
                    filtered.sort((a, b) => (a.cost || 0) - (b.cost || 0));
                } else if (sortCost === 'desc') {
                    filtered.sort((a, b) => (b.cost || 0) - (a.cost || 0));
                } else {
                    // Default sort by name
                    filtered.sort((a, b) => a.name.localeCompare(b.name));
                }

                // Clear and rebuild table
                tbody.innerHTML = '';
                
                if (filtered.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; color: #6b7280; padding: 2rem;">No drivers matching the selected criteria.</td></tr>';
                } else {
                    filtered.forEach(driver => {
                        const costCell = driver.element.querySelector('.cost-cell');
                        costCell.textContent = driver.cost !== null ? '₹' + driver.cost.toFixed(2) : '-';
                        tbody.appendChild(driver.element);
                    });
                }
            }

            // Calculate cost button
            document.getElementById('calculate-cost-btn').addEventListener('click', function() {
                if (!pickupCoords || !destCoords) {
                    alert('Please select valid addresses from suggestions for both pickup and destination.');
                    return;
                }
                
                const dist = calculateDistance(pickupCoords[0], pickupCoords[1], destCoords[0], destCoords[1]);
                
                drivers.forEach(d => {
                    if (dist > 50 && !d.outstation) {
                        d.cost = null; // Driver doesn't do outstation but distance is >50km
                    } else {
                        d.cost = dist * d.pricePerKm;
                    }
                });
                
                updateTableRows();
            });

            // Apply Filters button
            document.getElementById('apply-filters-btn').addEventListener('click', function(e) {
                e.preventDefault();
                updateTableRows();
                filterPanelVisible(false);
            });

            // Real-time filtering as user changes options
            document.querySelectorAll('.lang-checkbox, .outstation-radio, #cap-min, #cap-max, #sort-cost').forEach(element => {
                element.addEventListener('change', updateTableRows);
            });

            // Initialize table
            updateTableRows();
        });
    </script>

    <style>
        /* Footer styles */
        .private-footer {
            background-color: #7d5a9b;
            color: #d1d5db;
            padding: 1.5rem 0;
            margin-top: auto;
        }

        .private-footer .container {
            max-width: 80rem;
            margin: 0 auto;
            padding: 0 1.5rem;
            text-align: center;
        }

        .private-footer p {
            font-size: 0.9rem;
            font-weight: 400;
        }

        /* Responsive footer */
        @media (max-width: 768px) {
            .private-footer .container {
                padding: 0 1rem;
            }

            .private-footer p {
                font-size: 0.85rem;
            }
        }
    </style>
</body>
</html>