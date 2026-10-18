export interface ActivityMetadata {
  slug?: string;
  title?: string;
  property_type?: string;
  property_for?: string;
  city?: string;
  locality?: string;
  expected_price?: number;
  photo?: string | null;
  keyword?: string;
  budget?: string;
  bhk?: string;
  query_params?: Record<string, any>;
  [key: string]: any;
}

export interface ActivityRecord {
  id?: number;
  property_id?: number | null;
  activity_type: 'recent_search' | 'view_property' | 'shortlist' | 'contacted' | string;
  property?: any;
  metadata?: ActivityMetadata;
  created_at: string;
}

const GUEST_VIEWED_KEY = 'realhem_guest_activity';
const GUEST_SEARCHES_KEY = 'realhem_guest_searches';
const GUEST_SHORTLIST_KEY = 'realhem_guest_shortlist';
const GUEST_CONTACTED_KEY = 'realhem_guest_contacted';
const MAX_GUEST_ITEMS = 50;

export function useUserActivity() {
  const recentActivities = useState<ActivityRecord[]>('realhem_recent_activities', () => []);
  const recentSearches = useState<ActivityRecord[]>('realhem_recent_searches', () => []);
  const shortlistedProperties = useState<ActivityRecord[]>('realhem_shortlisted_properties', () => []);
  const contactedProperties = useState<ActivityRecord[]>('realhem_contacted_properties', () => []);
  const isLoaded = useState<boolean>('realhem_recent_loaded', () => false);

  const viewedCount = computed(() => recentActivities.value.length);
  const searchCount = computed(() => recentSearches.value.length);
  const shortlistCount = computed(() => shortlistedProperties.value.length);
  const contactedCount = computed(() => contactedProperties.value.length);

  // Backward compatibility for components expecting recentCount
  const recentCount = computed(() => recentActivities.value.length);

  const isAuthenticated = (): boolean => {
    if (!import.meta.client) return false;
    return !!localStorage.getItem('realhem_user_token');
  };

  /**
   * Helper to retrieve items from localStorage safely
   */
  const getLocalList = (key: string): ActivityRecord[] => {
    if (!import.meta.client) return [];
    try {
      const raw = localStorage.getItem(key);
      return raw ? JSON.parse(raw) : [];
    } catch {
      return [];
    }
  };

  const setLocalList = (key: string, list: ActivityRecord[]): void => {
    if (!import.meta.client) return;
    try {
      localStorage.setItem(key, JSON.stringify(list.slice(0, MAX_GUEST_ITEMS)));
    } catch (err) {
      console.warn(`Failed to save to localStorage (${key}):`, err);
    }
  };

  const getGuestActivities = (): ActivityRecord[] => getLocalList(GUEST_VIEWED_KEY);
  const getGuestSearches = (): ActivityRecord[] => getLocalList(GUEST_SEARCHES_KEY);
  const getGuestShortlist = (): ActivityRecord[] => getLocalList(GUEST_SHORTLIST_KEY);
  const getGuestContacted = (): ActivityRecord[] => getLocalList(GUEST_CONTACTED_KEY);

  const dispatchActivityEvent = (detail: any) => {
    if (!import.meta.client) return;
    window.dispatchEvent(new CustomEvent('realhem-activity-logged', { detail }));
  };

  /**
   * Check if a property is in the shortlist
   */
  const isShortlisted = (propertyId?: number): boolean => {
    if (!propertyId) return false;
    return shortlistedProperties.value.some(
      (item) => item.property_id === propertyId || item.property?.id === propertyId
    );
  };

  /**
   * Log a property view
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

    const existing = recentActivities.value.filter((act) => act.property_id !== property.id);
    recentActivities.value = [activityItem, ...existing].slice(0, MAX_GUEST_ITEMS);
    dispatchActivityEvent({ type: 'view_property', property });

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
      const local = getGuestActivities().filter((act) => act.property_id !== property.id);
      setLocalList(GUEST_VIEWED_KEY, [activityItem, ...local]);
    }
  };

  /**
   * Log a search query (Recent Searches)
   */
  const logSearch = async (searchData: {
    keyword?: string;
    city?: string;
    type?: string;
    bhk?: string;
    budget?: string;
    [key: string]: any;
  }): Promise<void> => {
    if (!import.meta.client) return;
    const kw = (searchData.keyword || '').trim();
    const city = (searchData.city || '').trim();
    if (!kw && !city && !searchData.type) return;

    const title = [
      searchData.bhk ? `${searchData.bhk} BHK` : null,
      searchData.type ? searchData.type.toUpperCase() : null,
      kw || (city ? `in ${city}` : 'All Properties'),
    ]
      .filter(Boolean)
      .join(' • ');

    const activityItem: ActivityRecord = {
      property_id: null,
      activity_type: 'recent_search',
      metadata: {
        title,
        keyword: kw,
        city,
        property_for: searchData.type || 'buy',
        bhk: searchData.bhk,
        budget: searchData.budget,
        query_params: searchData,
      },
      created_at: new Date().toISOString(),
    };

    // Deduplicate by keyword and city
    const existing = recentSearches.value.filter(
      (s) =>
        s.metadata?.keyword !== kw ||
        s.metadata?.city !== city ||
        s.metadata?.property_for !== searchData.type
    );
    recentSearches.value = [activityItem, ...existing].slice(0, MAX_GUEST_ITEMS);
    dispatchActivityEvent({ type: 'recent_search', searchData });

    if (isAuthenticated()) {
      try {
        const { post } = useApi();
        await post('/api/user/activities', {
          activity_type: 'recent_search',
          property_id: null,
          metadata: activityItem.metadata,
        });
      } catch (err) {
        console.warn('Could not log search to server:', err);
      }
    } else {
      const local = getGuestSearches().filter(
        (s) =>
          s.metadata?.keyword !== kw ||
          s.metadata?.city !== city ||
          s.metadata?.property_for !== searchData.type
      );
      setLocalList(GUEST_SEARCHES_KEY, [activityItem, ...local]);
    }
  };

  /**
   * Toggle Shortlist / Favourite
   */
  const toggleShortlist = async (property: any): Promise<boolean> => {
    if (!import.meta.client || !property || !property.id) return false;

    const propId = property.id;
    const currentlyShortlisted = isShortlisted(propId);

    if (currentlyShortlisted) {
      // Remove
      shortlistedProperties.value = shortlistedProperties.value.filter(
        (item) => item.property_id !== propId && item.property?.id !== propId
      );
      if (!isAuthenticated()) {
        const local = getGuestShortlist().filter(
          (item) => item.property_id !== propId && item.property?.id !== propId
        );
        setLocalList(GUEST_SHORTLIST_KEY, local);
      } else {
        try {
          const { post } = useApi();
          await post('/api/user/activities/shortlist', { property_id: propId });
        } catch (err) {
          console.warn('Failed to toggle shortlist on server:', err);
        }
      }
      dispatchActivityEvent({ type: 'shortlist_removed', propertyId: propId });
      return false;
    }

    // Add
    const activityItem: ActivityRecord = {
      property_id: propId,
      activity_type: 'shortlist',
      property: {
        id: property.id,
        slug: property.slug,
        title: property.title,
        property_type: property.property_type,
        property_for: property.property_for,
        city: property.city,
        locality: property.locality,
        expected_price: property.expected_price,
        carpet_area: property.carpet_area,
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
        carpet_area: property.carpet_area,
        photo: property.photos && property.photos.length > 0 ? property.photos[0] : null,
      },
      created_at: new Date().toISOString(),
    };

    shortlistedProperties.value = [activityItem, ...shortlistedProperties.value];
    dispatchActivityEvent({ type: 'shortlist_added', property });

    if (isAuthenticated()) {
      try {
        const { post } = useApi();
        await post('/api/user/activities/shortlist', {
          property_id: propId,
          metadata: activityItem.metadata,
        });
      } catch (err) {
        console.warn('Failed to shortlist on server:', err);
      }
    } else {
      const local = getGuestShortlist().filter(
        (item) => item.property_id !== propId && item.property?.id !== propId
      );
      setLocalList(GUEST_SHORTLIST_KEY, [activityItem, ...local]);
    }

    return true;
  };

  /**
   * Log Contacted property
   */
  const logContacted = async (property: any, contactType = 'phone'): Promise<void> => {
    if (!import.meta.client || !property || !property.id) return;

    const activityItem: ActivityRecord = {
      property_id: property.id,
      activity_type: 'contacted',
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
        contact_type: contactType,
        photo: property.photos && property.photos.length > 0 ? property.photos[0] : null,
      },
      created_at: new Date().toISOString(),
    };

    const existing = contactedProperties.value.filter((c) => c.property_id !== property.id);
    contactedProperties.value = [activityItem, ...existing].slice(0, MAX_GUEST_ITEMS);
    dispatchActivityEvent({ type: 'contacted', property });

    if (isAuthenticated()) {
      try {
        const { post } = useApi();
        await post('/api/user/activities', {
          activity_type: 'contacted',
          property_id: property.id,
          metadata: activityItem.metadata,
        });
      } catch (err) {
        console.warn('Could not log contact to server:', err);
      }
    } else {
      const local = getGuestContacted().filter((c) => c.property_id !== property.id);
      setLocalList(GUEST_CONTACTED_KEY, [activityItem, ...local]);
    }
  };

  /**
   * Load all activities (Searches, Viewed, Shortlist, Contacted)
   */
  const loadAllActivities = async () => {
    if (!import.meta.client) return;

    if (isAuthenticated()) {
      try {
        const { get } = useApi();
        const [viewRes, searchRes, shortRes, contRes]: any[] = await Promise.all([
          get('/api/user/activities?type=view_property&per_page=50'),
          get('/api/user/activities?type=recent_search&per_page=50'),
          get('/api/user/activities?type=shortlist&per_page=50'),
          get('/api/user/activities?type=contacted&per_page=50'),
        ]);

        recentActivities.value = viewRes?.data || [];
        recentSearches.value = searchRes?.data || [];
        shortlistedProperties.value = shortRes?.data || [];
        contactedProperties.value = contRes?.data || [];
        isLoaded.value = true;
        return;
      } catch (err) {
        console.warn('Failed to load user activities from API, using fallback:', err);
      }
    }

    // Guest fallback
    recentActivities.value = getGuestActivities();
    recentSearches.value = getGuestSearches();
    shortlistedProperties.value = getGuestShortlist();
    contactedProperties.value = getGuestContacted();
    isLoaded.value = true;
  };

  const loadRecentActivities = async (): Promise<ActivityRecord[]> => {
    await loadAllActivities();
    return recentActivities.value;
  };

  /**
   * Remove a single item from a specific activity list
   */
  const removeItem = async (type: string, idOrPropId: number | string) => {
    if (!import.meta.client) return;

    if (type === 'recent_search') {
      recentSearches.value = recentSearches.value.filter(
        (s) => s.id !== idOrPropId && s.metadata?.title !== idOrPropId
      );
      if (!isAuthenticated()) {
        setLocalList(
          GUEST_SEARCHES_KEY,
          getGuestSearches().filter((s) => s.id !== idOrPropId && s.metadata?.title !== idOrPropId)
        );
      }
    } else if (type === 'view_property') {
      recentActivities.value = recentActivities.value.filter(
        (v) => v.id !== idOrPropId && v.property_id !== idOrPropId
      );
      if (!isAuthenticated()) {
        setLocalList(
          GUEST_VIEWED_KEY,
          getGuestActivities().filter((v) => v.id !== idOrPropId && v.property_id !== idOrPropId)
        );
      }
    } else if (type === 'shortlist') {
      shortlistedProperties.value = shortlistedProperties.value.filter(
        (s) => s.id !== idOrPropId && s.property_id !== idOrPropId
      );
      if (!isAuthenticated()) {
        setLocalList(
          GUEST_SHORTLIST_KEY,
          getGuestShortlist().filter((s) => s.id !== idOrPropId && s.property_id !== idOrPropId)
        );
      }
    } else if (type === 'contacted') {
      contactedProperties.value = contactedProperties.value.filter(
        (c) => c.id !== idOrPropId && c.property_id !== idOrPropId
      );
      if (!isAuthenticated()) {
        setLocalList(
          GUEST_CONTACTED_KEY,
          getGuestContacted().filter((c) => c.id !== idOrPropId && c.property_id !== idOrPropId)
        );
      }
    }

    if (isAuthenticated() && typeof idOrPropId === 'number') {
      try {
        const { del } = useApi();
        await del(`/api/user/activities/${idOrPropId}`);
      } catch (err) {
        console.warn('Failed to delete activity on server:', err);
      }
    }

    dispatchActivityEvent({ type: 'item_removed', activityType: type, id: idOrPropId });
  };

  /**
   * Clear an entire activity module
   */
  const clearActivityType = async (type: string) => {
    if (!import.meta.client) return;

    if (type === 'recent_search') {
      recentSearches.value = [];
      localStorage.removeItem(GUEST_SEARCHES_KEY);
    } else if (type === 'view_property') {
      recentActivities.value = [];
      localStorage.removeItem(GUEST_VIEWED_KEY);
    } else if (type === 'shortlist') {
      shortlistedProperties.value = [];
      localStorage.removeItem(GUEST_SHORTLIST_KEY);
    } else if (type === 'contacted') {
      contactedProperties.value = [];
      localStorage.removeItem(GUEST_CONTACTED_KEY);
    }

    if (isAuthenticated()) {
      try {
        const { del } = useApi();
        await del(`/api/user/activities/clear?type=${type}`);
      } catch (err) {
        console.warn('Failed to clear activity on server:', err);
      }
    }

    dispatchActivityEvent({ type: 'module_cleared', activityType: type });
  };

  const clearGuestActivities = () => {
    clearActivityType('view_property');
  };

  /**
   * Bulk sync all guest activities to server on login
   */
  const syncGuestActivitiesToServer = async (): Promise<number> => {
    if (!import.meta.client || !isAuthenticated()) return 0;

    const allGuestItems = [
      ...getGuestSearches(),
      ...getGuestActivities(),
      ...getGuestShortlist(),
      ...getGuestContacted(),
    ];

    if (allGuestItems.length === 0) return 0;

    try {
      const { post } = useApi();
      const res: any = await post('/api/user/activities/sync', {
        activities: allGuestItems.map((item) => ({
          property_id: item.property_id || null,
          activity_type: item.activity_type,
          metadata: item.metadata || {},
          created_at: item.created_at,
        })),
      });

      if (res && res.status === 'success') {
        localStorage.removeItem(GUEST_SEARCHES_KEY);
        localStorage.removeItem(GUEST_VIEWED_KEY);
        localStorage.removeItem(GUEST_SHORTLIST_KEY);
        localStorage.removeItem(GUEST_CONTACTED_KEY);
        await loadAllActivities();
        return res.synced || allGuestItems.length;
      }
    } catch (err) {
      console.warn('Failed to sync guest activities to server:', err);
    }

    return 0;
  };

  const fetchUserActivities = async (type?: string, page = 1) => {
    if (!import.meta.client || !isAuthenticated()) {
      const guest = type === 'recent_search'
        ? getGuestSearches()
        : type === 'shortlist'
        ? getGuestShortlist()
        : type === 'contacted'
        ? getGuestContacted()
        : getGuestActivities();
      return { data: guest, total: guest.length };
    }

    try {
      const { get } = useApi();
      const query = new URLSearchParams();
      if (type) query.set('type', type);
      query.set('page', String(page));

      const res: any = await get(`/api/user/activities?${query.toString()}`);
      return res;
    } catch (err) {
      console.warn('Failed to fetch user activities:', err);
      return { data: [], total: 0 };
    }
  };

  return {
    recentActivities,
    recentSearches,
    shortlistedProperties,
    contactedProperties,
    recentCount,
    viewedCount,
    searchCount,
    shortlistCount,
    contactedCount,
    isLoaded,
    isAuthenticated,
    isShortlisted,
    logSearch,
    logPropertyView,
    toggleShortlist,
    logContacted,
    loadAllActivities,
    loadRecentActivities,
    removeItem,
    clearActivityType,
    clearGuestActivities,
    getGuestActivities,
    getGuestSearches,
    getGuestShortlist,
    getGuestContacted,
    syncGuestActivitiesToServer,
    fetchUserActivities,
  };
}
