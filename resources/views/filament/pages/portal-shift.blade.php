<x-filament-panels::page>
    <style>
        .portal-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem 0;
        }
        
        .shift-card { 
            background-color: #111214; 
            border: 1px solid #1c1f22; 
            border-radius: 20px; 
            padding: 3rem 2rem; 
            text-align: center; 
            transition: all 0.3s ease; 
            text-decoration: none; 
            display: block; 
            cursor: pointer;
            color: inherit;
        }
        
        .shift-icon { 
            font-size: 4.5rem; 
            margin-bottom: 1.5rem; 
        }
        
        .shift-title { 
            color: #f8fafc; 
            font-weight: 700; 
            font-size: 1.75rem; 
            margin-bottom: 0.75rem; 
        }
        
        .shift-card.morning:hover { 
            transform: translateY(-8px); 
            border-color: #f59e0b; 
            box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.2);
        }
        
        .shift-time-morning { 
            color: #fbbf24; 
            background: rgba(245, 158, 11, 0.1); 
            padding: 0.5rem 1rem; 
            border-radius: 999px; 
            font-weight: 600; 
            border: 1px solid rgba(245, 158, 11, 0.2); 
            display: inline-block;
        }

        .shift-card.evening:hover { 
            transform: translateY(-8px); 
            border-color: #6366f1; 
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.2);
        }
        
        .shift-time-evening { 
            color: #818cf8; 
            background: rgba(99, 102, 241, 0.1); 
            padding: 0.5rem 1rem; 
            border-radius: 999px; 
            font-weight: 600; 
            border: 1px solid rgba(99, 102, 241, 0.2); 
            display: inline-block;
        }
    </style>

    <div class="portal-container">
        <!-- Grid Filament (Sistem bawaan Tailwind) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Kartu Shift Pagi -->
            <div>
                <a href="/upload?shift=pagi" class="shift-card morning">
                    <div class="shift-icon">☀️</div>
                    <h2 class="shift-title">Shift Pagi</h2>
                    <div class="shift-time-morning">09.00 - 14:00 WITA</div>
                </a>
            </div>

            <!-- Kartu Shift Sore -->
            <div>
                <a href="/upload?shift=sore" class="shift-card evening">
                    <div class="shift-icon">🌙</div>
                    <h2 class="shift-title">Shift Sore</h2>
                    <div class="shift-time-evening">15:00 - 18.00 WITA</div>
                </a>
            </div>

        </div>
    </div>
</x-filament-panels::page>