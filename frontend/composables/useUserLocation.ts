export function useUserLocation() {
  const currentCity = useState<string>('realhem_city', () => 'Delhi NCR');
  const currentLocality = useState<string>('realhem_locality', () => '');
  const nearbyLocalities = useState<string[]>('realhem_nearby_localities', () => []);
  const isNearMeActive = useState<boolean>('realhem_is_near_me', () => false);
  const isDetectingLocation = ref(false);
  const locationToast = ref('');
  let toastTimeout: any = null;

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

  if (import.meta.client) {
    const saved = localStorage.getItem('realhem_city');
    if (saved && !currentCity.value) {
      currentCity.value = saved;
    }
  }

  const showToast = (message: string, duration = 3500) => {
    locationToast.value = message;
    if (toastTimeout) clearTimeout(toastTimeout);
    toastTimeout = setTimeout(() => {
      locationToast.value = '';
    }, duration);
  };

  const setCity = (cityName: string) => {
    if (!cityName) return;
    currentCity.value = cityName;
    if (import.meta.client) {
      localStorage.setItem('realhem_city', cityName);
      window.dispatchEvent(
        new CustomEvent('realhem-city-changed', {
          detail: { city: cityName },
        })
      );
    }
  };

  const fetchNearbyLocalities = async (city: string, locality = '', lat: number | null = null, lng: number | null = null) => {
    const { fetchWithCache } = useFrontendCache();
    try {
      const params = new URLSearchParams();
      if (city) params.set('city', city);
      if (locality) params.set('locality', locality);
      if (lat !== null && lat !== undefined && !isNaN(Number(lat))) params.set('lat', String(lat));
      if (lng !== null && lng !== undefined && !isNaN(Number(lng))) params.set('lng', String(lng));

      const { apiBase } = useApi();
      const url = `${apiBase}/api/locations/nearby?${params.toString()}`;
      const data: any = await fetchWithCache(url, {
        ttlMs: 30 * 60 * 1000,
        swr: true,
      });

      if (data && data.nearby_localities && data.nearby_localities.length > 0) {
        nearbyLocalities.value = data.nearby_localities;
      }
      return data?.nearby_localities || [];
    } catch (err) {
      console.warn('Failed to fetch nearby localities:', err);
      return [];
    }
  };

  const removeNearbyLocality = (localityName: string) => {
    nearbyLocalities.value = nearbyLocalities.value.filter((item) => item !== localityName);
  };

  const clearNearbyLocalities = () => {
    nearbyLocalities.value = [];
    isNearMeActive.value = false;
  };

  const detectLocation = async (onDetected: ((data: any) => void) | null = null) => {
    isDetectingLocation.value = true;
    showToast('📍 Detecting your current location...', 8000);

    const { apiBase } = useApi();

    const applyDetectedData = (data: any) => {
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

    if (typeof window === 'undefined' || !navigator.geolocation) {
      try {
        const res = await fetch(`${apiBase}/api/locations/detect`);
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
          const res = await fetch(`${apiBase}/api/locations/detect?lat=${latitude}&lng=${longitude}`);
          const data = await res.json();
          applyDetectedData(data);
        } catch (err) {
          console.warn('Backend location detect failed, attempting client geocode fallback:', err);
          try {
            const directRes = await fetch(
              `https://nominatim.openstreetmap.org/reverse?lat=${latitude}&lon=${longitude}&format=json`,
              { headers: { Accept: 'application/json' } }
            );
            const directData = await directRes.json();
            const addr = directData.address || {};
            const rawCity = addr.city || addr.town || addr.state_district || 'Ahmedabad';
            const locality = addr.suburb || addr.neighbourhood || addr.road || '';

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
          const res = await fetch(`${apiBase}/api/locations/detect`);
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

