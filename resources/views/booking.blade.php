<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Provider Booking</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --bg: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --border: rgba(255, 255, 255, 0.1);
            --text-main: #f8fafc;
            --text-dim: #94a3b8;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            min-height: 100vh;
            background: radial-gradient(circle at top right, #1e1b4b, #0f172a);
        }

        .container {
            width: 100%;
            max-width: 1000px;
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 2rem;
            background: linear-gradient(to right, #818cf8, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
        }

        .card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .provider-item {
            padding: 12px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 8px;
            border: 1px solid transparent;
        }

        .provider-item:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .provider-item.active {
            background: rgba(99, 102, 241, 0.1);
            border-color: var(--primary);
        }

        .slot-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 12px;
            margin-top: 20px;
        }

        .slot-button {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            color: var(--text-main);
            padding: 12px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
            font-weight: 500;
        }

        .slot-button:hover:not(:disabled) {
            background: var(--primary);
            border-color: var(--primary);
            transform: translateY(-2px);
        }

        .slot-button:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .slot-button.selected {
            background: var(--primary);
            border-color: var(--primary);
            box-shadow: 0 0 15px rgba(99, 102, 241, 0.4);
        }

        input[type="date"] {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            color: white;
            padding: 10px;
            border-radius: 8px;
            width: 100%;
            margin-bottom: 20px;
        }

        .btn-book {
            background: var(--primary);
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            transition: all 0.2s;
        }

        .btn-book:hover:not(:disabled) {
            background: var(--primary-hover);
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.3);
        }

        .btn-book:disabled {
            background: #475569;
            cursor: not-allowed;
        }

        [x-cloak] { display: none !important; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: var(--bg); }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.2); }
    </style>
</head>
<body x-data="bookingApp()">
    <div class="container">
        <h1>Appointment Booking</h1>

        <div class="grid">
            <!-- Sidebar: Providers -->
            <div class="card">
                <h3 style="margin-top: 0;">Service Providers</h3>
                <div style="max-height: 500px; overflow-y: auto;">
                    <template x-for="provider in providers" :key="provider.id">
                        <div class="provider-item" 
                             :class="{ 'active': selectedProvider?.id === provider.id }"
                             @click="selectProvider(provider)">
                            <div style="font-weight: 600;" x-text="provider.name"></div>
                            <div style="font-size: 0.85rem; color: var(--text-dim);" x-text="provider.service_type"></div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Main: Calendar & Slots -->
            <div class="card" x-show="selectedProvider" x-cloak>
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 2rem;">
                    <div>
                        <h2 style="margin: 0;" x-text="selectedProvider?.name"></h2>
                        <p style="color: var(--text-dim); margin-top: 4px;" x-text="selectedProvider?.description"></p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 500;">Select Date</label>
                        <input type="date" x-model="selectedDate" @change="fetchSlots()">
                    </div>
                    <div>
                        <!-- Status/Summary -->
                        <div style="background: rgba(255, 255, 255, 0.05); padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                            <div style="color: var(--text-dim); font-size: 0.85rem;">Working Hours</div>
                            <div style="font-weight: 600;" x-text="`${selectedProvider?.working_hours?.start} - ${selectedProvider?.working_hours?.end}`"></div>
                        </div>
                    </div>
                </div>

                <div x-show="loadingSlots" class="text-dim">Loading available slots...</div>
                
                <div x-show="!loadingSlots && slots.length === 0" class="text-dim" style="margin-top: 20px;">
                    No available slots for this date.
                </div>

                <div x-show="!loadingSlots && slots.length > 0">
                    <label style="display: block; margin-bottom: 12px; font-weight: 500; margin-top: 20px;">Available Slots</label>
                    <div class="slot-grid">
                        <template x-for="slot in slots">
                            <button class="slot-button" 
                                    :class="{ 'selected': selectedSlot === slot }"
                                    @click="selectedSlot = slot"
                                    x-text="slot"></button>
                        </template>
                    </div>

                    <button class="btn-book" 
                            :disabled="!selectedSlot || bookingInProgress"
                            @click="bookNow()">
                        <span x-show="!bookingInProgress">Book Appointment</span>
                        <span x-show="bookingInProgress">Booking...</span>
                    </button>
                </div>
            </div>

            <div class="card" x-show="!selectedProvider" style="display: flex; align-items: center; justify-content: center; color: var(--text-dim);">
                Select a provider to see availability
            </div>
        </div>
    </div>

    <script>
        function bookingApp() {
            return {
                providers: [],
                selectedProvider: null,
                selectedDate: new Date().toISOString().split('T')[0],
                slots: [],
                selectedSlot: null,
                loadingSlots: false,
                bookingInProgress: false,

                init() {
                    this.fetchProviders();
                },

                async fetchProviders() {
                    try {
                        const response = await axios.get('/api/providers');
                        this.providers = response.data;
                    } catch (error) {
                        console.error('Error fetching providers:', error);
                    }
                },

                selectProvider(provider) {
                    this.selectedProvider = provider;
                    this.selectedSlot = null;
                    this.fetchSlots();
                },

                async fetchSlots() {
                    if (!this.selectedProvider || !this.selectedDate) return;
                    this.loadingSlots = true;
                    this.selectedSlot = null;
                    try {
                        const response = await axios.get(`/api/providers/${this.selectedProvider.id}/slots?date=${this.selectedDate}`);
                        this.slots = response.data.available_slots || [];
                    } catch (error) {
                        console.error('Error fetching slots:', error);
                        this.slots = [];
                    } finally {
                        this.loadingSlots = false;
                    }
                },

                async bookNow() {
                    if (!this.selectedSlot) return;
                    this.bookingInProgress = true;
                    try {
                        // For demo, we assume user_id 1 exists (we'll seed it)
                        const response = await axios.post('/api/bookings', {
                            user_id: 1,
                            provider_id: this.selectedProvider.id,
                            date: this.selectedDate,
                            time_slot: this.selectedSlot
                        });
                        alert(response.data.message);
                        this.fetchSlots(); // Refresh
                    } catch (error) {
                        alert(error.response?.data?.message || 'Booking failed');
                    } finally {
                        this.bookingInProgress = false;
                    }
                }
            }
        }
    </script>
</body>
</html>
