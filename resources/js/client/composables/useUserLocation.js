import { ref } from 'vue';

const currentCity = ref(localStorage.getItem('realhem_city') || 'Delhi NCR');
const currentLocality = ref('');
const nearbyLocalities = ref([]);
const isNearMeActive = ref(false);
const isDetectingLocation = ref(false);
const locationToast = ref('');
let toastTimeout = null;

const availableCities = [
    'Delhi NCR',
    'Ahmedabad',
    'Mumbai',
    'Bangalore',
    'Pune',
    'Hyderabad',
    'Chennai',
    'Kolkata',
];

export function useUserLocation() {
    const showToast = (message, duration = 3500) => {
        locationToast.value = message;
        if (toastTimeout) clearTimeout(toastTimeout);
        toastTimeout = setTimeout(() => {
            locationToast.value = '';
        }, duration);
    };

    const setCity = (cityName) => {
        if (!cityName) return;
        currentCity.value = cityName;
        localStorage.setItem('realhem_city', cityName);
        window.dispatchEvent(new CustomEvent('realhem-city-changed', {
            detail: { city: cityName },
        }));
    };

    /**
     * Fetch nearby localities within 3-5 km for a given city and locality.
     */
    const fetchNearbyLocalities = async (city, locality = '') => {
        try {
            const res = await fetch(`/api/locations/nearby?city=${encodeURIComponent(city)}&locality=${encodeURIComponent(locality || '')}`);
            const data = await res.json();
            if (data.nearby_localities && data.nearby_localities.length > 0) {
                nearbyLocalities.value = data.nearby_localities;
            }
            return data.nearby_localities || [];
        } catch (err) {
            console.warn('Failed to fetch nearby localities:', err);
            return [];
        }
    };

    const removeNearbyLocality = (localityName) => {
        nearbyLocalities.value = nearbyLocalities.value.filter((item) => item !== localityName);
    };

    const clearNearbyLocalities = () => {
        nearbyLocalities.value = [];
        isNearMeActive.value = false;
    };

    /**
     * Detect user's current city and locality via browser GPS and API.
     * @param {Function} onDetected Callback receives { city, locality, display, lat, lng, nearby_localities }
     */
    const detectLocation = async (onDetected = null) => {
        isDetectingLocation.value = true;
        showToast('📍 Detecting your current location...', 8000);

        const applyDetectedData = (data) => {
            if (data.city) {
                setCity(data.city);
            }
            if (data.locality) {
                currentLocality.value = data.locality;
            }
            if (data.nearby_localities && Array.isArray(data.nearby_localities)) {
                nearbyLocalities.value = data.nearby_localities;
            }
            isNearMeActive.value = true;

            const displayMsg = data.display || data.city || 'Location detected';
            showToast(`📍 Location detected: ${displayMsg}`);

            if (onDetected && typeof onDetected === 'function') {
                onDetected(data);
            }
            isDetectingLocation.value = false;
        };

        if (!navigator.geolocation) {
            try {
                const res = await fetch('/api/locations/detect');
                const data = await res.json();
                applyDetectedData(data);
            } catch {
                isDetectingLocation.value = false;
                showToast('Unable to detect location. Please select your city.');
            }
            return;
        }

        navigator.geolocation.getCurrentPosition(
            async (pos) => {
                const { latitude, longitude } = pos.coords;
                try {
                    const res = await fetch(`/api/locations/detect?lat=${latitude}&lng=${longitude}`);
                    const data = await res.json();
                    applyDetectedData(data);
                } catch (err) {
                    console.warn('Backend location detect failed, attempting client geocode fallback:', err);
                    try {
                        const directRes = await fetch(
                            `https://nominatim.openstreetmap.org/reverse?lat=${latitude}&lon=${longitude}&format=json`,
                            { headers: { 'Accept': 'application/json' } }
                        );
                        const directData = await directRes.json();
                        const addr = directData.address || {};
                        const rawCity = addr.city || addr.town || addr.state_district || 'Ahmedabad';
                        const locality = addr.suburb || addr.neighbourhood || addr.road || '';
                        
                        // Also load nearby localities for this city
                        const nearby = await fetchNearbyLocalities(rawCity, locality);
                        applyDetectedData({
                            city: rawCity,
                            locality,
                            display: locality ? `${locality}, ${rawCity}` : rawCity,
                            nearby_localities: nearby,
                            lat: latitude,
                            lng: longitude,
                        });
                    } catch {
                        isDetectingLocation.value = false;
                        showToast('Could not resolve your area. Please pick your city.');
                    }
                }
            },
            async (err) => {
                console.warn('Browser geolocation error:', err.message);
                try {
                    const res = await fetch('/api/locations/detect');
                    const data = await res.json();
                    applyDetectedData(data);
                    showToast(`Location permission unavailable. Showing nearby properties in ${data.city}.`);
                } catch {
                    isDetectingLocation.value = false;
                    showToast('Location permission denied. Please choose your city.');
                }
            },
            {
                enableHighAccuracy: true,
                timeout: 7000,
                maximumAge: 60000,
            }
        );
    };

    return {
        currentCity,
        currentLocality,
        nearbyLocalities,
        isNearMeActive,
        isDetectingLocation,
        locationToast,
        availableCities,
        setCity,
        detectLocation,
        fetchNearbyLocalities,
        removeNearbyLocality,
        clearNearbyLocalities,
    };
}
