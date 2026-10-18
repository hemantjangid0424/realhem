export interface ActivityMetadata {
  slug?: string;
  title?: string;
  property_type?: string;
  property_for?: string;
  city?: string;
  locality?: string;
  expected_price?: number;
  photo?: string | null;
  [key: string]: any;
}

export interface ActivityRecord {
  id?: number;
  property_id?: number;
  activity_type: string;
  property?: any;
  metadata?: ActivityMetadata;
  created_at: string;
}

const GUEST_ACTIVITY_KEY = 'realhem_guest_activity';
const MAX_GUEST_ACTIVITIES = 50;

export function useUserActivity() {
  const recentActivities = useState<ActivityRecord[]>('realhem_recent_activities', () => []);
  const isLoaded = useState<boolean>('realhem_recent_loaded', () => false);

  const recentCount = computed(() => recentActivities.value.length);

  /**
   * Check if current user is logged in
   */
  const isAuthenticated = (): boolean => {
    if (!import.meta.client) return false;
    return !!localStorage.getItem('realhem_user_token');
  };

  /**
   * Retrieve guest activities stored locally in browser
   */
  const getGuestActivities = (): ActivityRecord[] => {
    if (!import.meta.client) return [];
    try {
      const raw = localStorage.getItem(GUEST_ACTIVITY_KEY);
      return raw ? JSON.parse(raw) : [];
    } catch {
      return [];
    }
  };

  /**
   * Save an activity locally in browser for guest users
   */
  const saveGuestActivity = (item: ActivityRecord): void => {
    if (!import.meta.client) return;
    try {
      const existing = getGuestActivities();
      // Remove previous entry for the same property to avoid duplication & bump to top
      const filtered = item.property_id
        ? existing.filter((act) => act.property_id !== item.property_id)
        : existing;

      filtered.unshift(item);

      // Keep only up to max activities
      const trimmed = filtered.slice(0, MAX_GUEST_ACTIVITIES);
      localStorage.setItem(GUEST_ACTIVITY_KEY, JSON.stringify(trimmed));
      recentActivities.value = trimmed;

      window.dispatchEvent(
        new CustomEvent('realhem-activity-logged', {
          detail: item,
        })
      );
    } catch (err) {
      console.warn('Failed to save guest activity locally:', err);
    }
  };

  /**
   * Clear all locally saved guest activities
   */
  const clearGuestActivities = (): void => {
    if (!import.meta.client) return;
    try {
      localStorage.removeItem(GUEST_ACTIVITY_KEY);
      if (!isAuthenticated()) {
        recentActivities.value = [];
      }
    } catch {
      // ignore
    }
  };

  /**
   * Load recent activities into shared reactive state
   */
  const loadRecentActivities = async (): Promise<ActivityRecord[]> => {
    if (!import.meta.client) return [];

    if (isAuthenticated()) {
      try {
        const { get } = useApi();
        const res: any = await get('/api/user/activities?type=view_property&per_page=20');
        const list = res?.data || (Array.isArray(res) ? res : []);
        recentActivities.value = list;
        isLoaded.value = true;
        return list;
      } catch (err) {
        console.warn('Failed to load user activities from API:', err);
      }
    }

    // Fallback or guest
    const guestList = getGuestActivities();
    recentActivities.value = guestList;
    isLoaded.value = true;
    return guestList;
  };

  /**
   * Log a property view activity.
   * If logged in -> sends to Laravel backend API.
   * If not logged in -> persists in browser localStorage as guest activity.
   */
  const logPropertyView = async (property: any): Promise<void> => {
    if (!import.meta.client || !property || !property.id) return;

    const activityItem: ActivityRecord = {
      property_id: property.id,
      activity_type: 'view_property',
      property: {
        id: property.id,
        slug: property.slug,
        title: property.title,
        property_type: property.property_type,
        property_for: property.property_for,
        city: property.city,
        locality: property.locality,
        expected_price: property.expected_price,
        photos: property.photos || [],
      },
      metadata: {
        slug: property.slug,
        title: property.title,
        property_type: property.property_type,
        property_for: property.property_for,
        city: property.city,
        locality: property.locality,
        expected_price: property.expected_price,
        photo: property.photos && property.photos.length > 0 ? property.photos[0] : null,
      },
      created_at: new Date().toISOString(),
    };

    // Update client reactive state immediately
    const existing = recentActivities.value.filter((act) => act.property_id !== property.id);
    recentActivities.value = [activityItem, ...existing].slice(0, MAX_GUEST_ACTIVITIES);

    window.dispatchEvent(
      new CustomEvent('realhem-activity-logged', {
        detail: activityItem,
      })
    );

    if (isAuthenticated()) {
      try {
        const { post } = useApi();
        await post('/api/user/activities', {
          activity_type: activityItem.activity_type,
          property_id: activityItem.property_id,
          metadata: activityItem.metadata,
        });
      } catch (err) {
        console.warn('Could not log activity to server:', err);
      }
    } else {
      saveGuestActivity(activityItem);
    }
  };

  /**
   * Sync guest activities to server when user logs in
   */
  const syncGuestActivitiesToServer = async (): Promise<number> => {
    if (!import.meta.client || !isAuthenticated()) return 0;

    const guestActs = getGuestActivities();
    if (guestActs.length === 0) return 0;

    try {
      const { post } = useApi();
      const res: any = await post('/api/user/activities/sync', {
        activities: guestActs.map((item) => ({
          property_id: item.property_id,
          activity_type: item.activity_type || 'view_property',
          metadata: item.metadata || {},
          created_at: item.created_at,
        })),
      });

      if (res && res.status === 'success') {
        clearGuestActivities();
        await loadRecentActivities();
        return res.synced || guestActs.length;
      }
    } catch (err) {
      console.warn('Failed to sync guest activities to server:', err);
    }

    return 0;
  };

  /**
   * Fetch authenticated user's activity history from server
   */
  const fetchUserActivities = async (type?: string, page = 1) => {
    if (!import.meta.client || !isAuthenticated()) {
      const guest = getGuestActivities();
      recentActivities.value = guest;
      return { data: guest, total: guest.length };
    }

    try {
      const { get } = useApi();
      const query = new URLSearchParams();
      if (type) query.set('type', type);
      query.set('page', String(page));

      const res: any = await get(`/api/user/activities?${query.toString()}`);
      if (res?.data) {
        recentActivities.value = res.data;
      }
      return res;
    } catch (err) {
      console.warn('Failed to fetch user activities:', err);
      return { data: [], total: 0 };
    }
  };

  return {
    recentActivities,
    recentCount,
    isLoaded,
    isAuthenticated,
    getGuestActivities,
    saveGuestActivity,
    clearGuestActivities,
    loadRecentActivities,
    logPropertyView,
    syncGuestActivitiesToServer,
    fetchUserActivities,
  };
}
