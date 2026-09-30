<x-app-layout title="Carte des Collines">
    <div class="mb-6 flex items-start justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Carte des Collines</h1>
            <p class="text-gray-500 text-sm mt-1">Visualisez les besoins et disponibilités géographiquement</p>
        </div>
        <div class="flex items-center gap-2 text-xs text-gray-500 bg-white rounded-lg border border-gray-100 px-3 py-2">
            <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
            <span id="last-update">Chargement…</span>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <select id="filtre-produit"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                <option value="">Tous les produits</option>
                @foreach($produits as $p)
                    <option value="{{ $p->id }}">{{ $p->nom }}</option>
                @endforeach
            </select>

            <select id="filtre-commune"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                <option value="">Toutes les communes</option>
                @foreach($communes as $c)
                    <option value="{{ $c->id }}">{{ $c->nom }}</option>
                @endforeach
            </select>

            <select id="filtre-type"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                <option value="">Besoins + Disponibilités</option>
                <option value="besoins">Besoins uniquement</option>
                <option value="dispos">Disponibilités uniquement</option>
            </select>

            <button id="btn-appliquer"
                    class="bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center justify-center gap-2">
                <x-icon name="filter" size="4" />
                Appliquer
            </button>
        </div>
    </div>

    {{-- Légende + compteurs --}}
    <div class="flex flex-wrap items-center gap-6 mb-4 text-sm">
        <div class="flex items-center gap-2">
            <div class="w-3 h-3 rounded-full bg-amber-500 border-2 border-white shadow"></div>
            <span class="text-gray-700">Besoins <span id="count-besoins" class="font-semibold text-gray-900">(0)</span></span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-3 h-3 rounded-full bg-green-600 border-2 border-white shadow"></div>
            <span class="text-gray-700">Disponibilités <span id="count-dispos" class="font-semibold text-gray-900">(0)</span></span>
        </div>
        <div class="flex items-center gap-2 text-xs text-gray-500">
            <x-icon name="info" size="3" />
            Taille du point = quantité · Couleur du popup = taux de couverture
        </div>
    </div>

    {{-- Carte --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-2">
        <div id="map" class="w-full rounded-lg" style="height: 620px;"></div>
    </div>

    {{-- Leaflet CSS + JS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        .leaflet-popup-content-wrapper {
            border-radius: 12px;
            padding: 4px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .leaflet-popup-content {
            margin: 12px 16px;
            font-family: Inter, sans-serif;
            min-width: 220px;
        }
        .popup-title {
            font-weight: 700;
            font-size: 15px;
            color: #111;
            margin-bottom: 4px;
        }
        .popup-meta {
            font-size: 12px;
            color: #666;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .popup-qte {
            font-weight: 700;
            font-size: 16px;
            color: #111;
            margin: 8px 0;
        }
        .popup-link {
            font-size: 12px;
            color: #16a34a;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-top: 8px;
        }
        .popup-link:hover { text-decoration: underline; }
        .popup-badge {
            display: inline-block;
            font-size: 10px;
            padding: 3px 8px;
            border-radius: 999px;
            margin: 4px 0;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .popup-progress {
            height: 6px;
            background: #f3f4f6;
            border-radius: 999px;
            overflow: hidden;
            margin: 8px 0;
        }
        .popup-progress-bar {
            height: 100%;
            border-radius: 999px;
            transition: width 0.5s;
        }
        .leaflet-popup-tip { box-shadow: 0 3px 14px rgba(0,0,0,0.1); }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ─── Initialiser la carte ─────────────────────────────
            const map = L.map('map', {
                zoomControl: true,
                scrollWheelZoom: true,
            }).setView([7.9333, 2.1833], 9);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 18,
            }).addTo(map);

            // ─── Groupes de marqueurs ─────────────────────────────
            const besoinsLayer = L.layerGroup().addTo(map);
            const disposLayer  = L.layerGroup().addTo(map);

            // ─── Fonction de création d'icône ─────────────────────
            function creerIcone(couleur, taille = 14) {
                return L.divIcon({
                    html: `
                        <div style="
                            background:${couleur};
                            width:${taille}px;
                            height:${taille}px;
                            border-radius:50%;
                            border:3px solid white;
                            box-shadow:0 2px 8px rgba(0,0,0,0.35);
                            transition:transform 0.2s;
                        "></div>`,
                    className: 'marker-point',
                    iconSize: [taille, taille],
                    iconAnchor: [taille/2, taille/2],
                    popupAnchor: [0, -taille/2],
                });
            }

            // ─── Formatage nombre ─────────────────────────────────
            function fmt(n) {
                return new Intl.NumberFormat('fr-FR').format(n);
            }

            // ─── Couleur selon taux de couverture ────────────────
            function couleurTaux(taux) {
                if (taux >= 100) return '#16a34a';   // vert
                if (taux >= 70)  return '#84cc16';   // lime
                if (taux >= 40)  return '#f59e0b';   // ambre
                return '#dc2626';                    // rouge
            }

            // ─── Taille selon quantité ────────────────────────────
            function tailleQuantite(quantite) {
                const taille = Math.sqrt(quantite / 100) * 2.5;
                return Math.min(36, Math.max(12, taille));
            }

            // ─── Charger les données ──────────────────────────────
            let autoRefresh = null;

            async function charger() {
                besoinsLayer.clearLayers();
                disposLayer.clearLayers();

                const produit = document.getElementById('filtre-produit').value;
                const commune = document.getElementById('filtre-commune').value;
                const type    = document.getElementById('filtre-type').value;

                const params = new URLSearchParams();
                if (produit) params.append('produit_id', produit);
                if (commune) params.append('commune_id', commune);

                try {
                    const res = await fetch('/api/carte/data?' + params.toString());
                    const data = await res.json();

                    // ─── Besoins ──────────────────────────────────
                    if (type !== 'dispos') {
                        data.besoins.forEach(b => {
                            const couleur = couleurTaux(b.taux);
                            const taille  = tailleQuantite(b.quantite);
                            const ic = creerIcone('#f59e0b', taille);

                            const pct = Math.min(100, Math.max(0, b.taux));
                            const html = `
                                <div class="popup-title">${b.titre}</div>
                                <div class="popup-meta">🏭 ${b.unite}</div>
                                <div class="popup-meta">📍 ${b.commune}</div>
                                <div class="popup-qte">${fmt(b.quantite)} kg recherchés</div>
                                <div class="popup-progress">
                                    <div class="popup-progress-bar" style="width:${pct}%;background:${couleur};"></div>
                                </div>
                                <span class="popup-badge" style="background:${couleur}20;color:${couleur};">
                                    Couvert à ${pct.toFixed(0)} %
                                </span>
                                <div class="popup-meta" style="margin-top:8px;">📅 Échéance : ${b.delai}</div>
                                <a href="${b.url}" class="popup-link">
                                    Voir le détail →
                                </a>
                            `;
                            L.marker([b.lat, b.lng], { icon: ic })
                                .bindPopup(html, { maxWidth: 300 })
                                .addTo(besoinsLayer);
                        });
                    }

                    // ─── Disponibilités ───────────────────────────
                    if (type !== 'besoins') {
                        data.dispos.forEach(d => {
                            const taille = tailleQuantite(d.quantite);
                            const ic = creerIcone('#16a34a', taille);

                            const badge = d.statut === 'declaree'
                                ? '<span class="popup-badge" style="background:#dbeafe;color:#1d4ed8;">Libre</span>'
                                : '<span class="popup-badge" style="background:#fef3c7;color:#92400e;">Associée</span>';

                            const html = `
                                <div class="popup-title">${d.titre}</div>
                                <div class="popup-meta">👤 ${d.producteur}</div>
                                <div class="popup-meta">📍 ${d.commune}</div>
                                <div class="popup-qte">${fmt(d.quantite)} kg disponibles</div>
                                ${badge}
                                <div class="popup-meta" style="margin-top:8px;">📅 Disponible le : ${d.date}</div>
                            `;
                            L.marker([d.lat, d.lng], { icon: ic })
                                .bindPopup(html, { maxWidth: 300 })
                                .addTo(disposLayer);
                        });
                    }

                    // ─── Compteurs ────────────────────────────────
                    document.getElementById('count-besoins').textContent = `(${data.besoins.length})`;
                    document.getElementById('count-dispos').textContent  = `(${data.dispos.length})`;

                    // ─── Dernière mise à jour ─────────────────────
                    const now = new Date();
                    document.getElementById('last-update').textContent =
                        'Mis à jour à ' + now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });

                } catch (e) {
                    console.error('Erreur chargement carte :', e);
                    document.getElementById('last-update').textContent = 'Erreur de chargement';
                }
            }

            // ─── Premier chargement ───────────────────────────────
            charger();

            // ─── Rafraîchissement automatique toutes les 30s ──────
            autoRefresh = setInterval(charger, 30000);

            // ─── Filtres ──────────────────────────────────────────
            document.getElementById('btn-appliquer').addEventListener('click', charger);
            document.getElementById('filtre-produit').addEventListener('change', charger);
            document.getElementById('filtre-commune').addEventListener('change', charger);
            document.getElementById('filtre-type').addEventListener('change', charger);

            // ─── Stopper le refresh quand on quitte la page ──────
            window.addEventListener('beforeunload', () => {
                if (autoRefresh) clearInterval(autoRefresh);
            });
        });
    </script>
</x-app-layout>