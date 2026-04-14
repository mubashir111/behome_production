'use client';

import { useState, useEffect, useCallback } from 'react';
import { apiFetch } from '@/lib/api';

interface Notification {
  id: string;
  title: string;
  body: string;
  icon: string;
  color: string;
  link: string;
  time: string;
  created_at: string;
  is_read: boolean;
}

const iconSvg: Record<string, string> = {
  cart:    'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
  check:   'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
  truck:   'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z',
  gift:    'M20 12v10H4V12M22 7H2v5h20V7zM12 22V7',
  x:       'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
  warning: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
  return:  'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6',
  bell:    'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
};

export default function NotificationsPage() {
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [loading, setLoading]             = useState(true);
  const [marking, setMarking]             = useState(false);
  const [loggedIn, setLoggedIn]           = useState(false);
  const [filter, setFilter]               = useState<'all' | 'unread'>('all');

  useEffect(() => {
    const token = localStorage.getItem('token');
    if (!token) { setLoading(false); return; }
    setLoggedIn(true);
  }, []);

  const fetchAll = useCallback(async () => {
    setLoading(true);
    try {
      const res = await apiFetch('/frontend/notifications');
      if (res?.data) setNotifications(res.data);
    } catch { /* silent */ }
    finally { setLoading(false); }
  }, []);

  const markAllRead = useCallback(async () => {
    if (marking) return;
    setMarking(true);
    try {
      await apiFetch('/frontend/notifications/mark-read', { method: 'POST' });
      setNotifications(prev => prev.map(n => ({ ...n, is_read: true })));
    } catch { /* silent */ }
    finally { setMarking(false); }
  }, [marking]);

  useEffect(() => { if (loggedIn) fetchAll(); }, [loggedIn, fetchAll]);

  const displayed = filter === 'unread' ? notifications.filter(n => !n.is_read) : notifications;

  if (!loggedIn) {
    return (
      <div style={{ minHeight: '60vh', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
        <div style={{ textAlign: 'center' }}>
          <p style={{ color: 'rgba(255,255,255,0.5)', fontSize: '16px' }}>Please log in to view your notifications.</p>
          <a href="/login" style={{ color: '#818cf8', textDecoration: 'none', fontWeight: 600 }}>Go to login →</a>
        </div>
      </div>
    );
  }

  return (
    <div className="container" style={{ maxWidth: '720px', padding: '40px 16px 80px' }}>
      {/* Page Header */}
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '24px' }}>
        <div>
          <h1 style={{ fontSize: '22px', fontWeight: 700, color: '#fff', margin: 0 }}>Notifications</h1>
          <p style={{ fontSize: '13px', color: 'rgba(255,255,255,0.4)', margin: '4px 0 0' }}>
            Your orders, updates &amp; announcements
          </p>
        </div>
        <button
          onClick={markAllRead}
          disabled={marking}
          style={{ fontSize: '12px', color: marking ? 'rgba(129,140,248,0.4)' : '#818cf8', background: 'none', border: 'none', cursor: marking ? 'default' : 'pointer', fontWeight: 600, transition: 'color .15s' }}
        >
          {marking ? 'Marking…' : 'Mark all as read'}
        </button>
      </div>

      {/* Filter tabs */}
      <div style={{ display: 'flex', gap: '8px', marginBottom: '20px' }}>
        {(['all', 'unread'] as const).map(f => (
          <button key={f} onClick={() => setFilter(f)} style={{
            padding: '6px 16px', borderRadius: '20px', border: 'none', cursor: 'pointer', fontSize: '12px', fontWeight: 600,
            background: filter === f ? '#6366f1' : 'rgba(255,255,255,0.06)',
            color: filter === f ? '#fff' : 'rgba(255,255,255,0.5)',
          }}>
            {f === 'all' ? 'All' : 'Unread'}
            {f === 'unread' && notifications.filter(n => !n.is_read).length > 0 && (
              <span style={{ marginLeft: '6px', background: '#ef4444', color: '#fff', borderRadius: '10px', padding: '0 5px', fontSize: '10px' }}>
                {notifications.filter(n => !n.is_read).length}
              </span>
            )}
          </button>
        ))}
      </div>

      {/* Notification list */}
      <div style={{ background: '#1a1a2e', borderRadius: '16px', border: '1px solid rgba(255,255,255,0.08)', overflow: 'hidden' }}>
        {loading && (
          <div style={{ padding: '48px', textAlign: 'center', color: 'rgba(255,255,255,0.3)', fontSize: '14px' }}>
            Loading notifications…
          </div>
        )}
        {!loading && displayed.length === 0 && (
          <div style={{ padding: '64px 32px', textAlign: 'center' }}>
            <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,0.15)" strokeWidth={1} style={{ margin: '0 auto 12px', display: 'block' }}>
              <path strokeLinecap="round" strokeLinejoin="round" d={iconSvg.bell} />
            </svg>
            <p style={{ color: 'rgba(255,255,255,0.3)', fontSize: '14px', margin: 0 }}>
              {filter === 'unread' ? 'No unread notifications' : 'No notifications yet'}
            </p>
          </div>
        )}
        {!loading && displayed.map((n, i) => (
          <a key={n.id} href={n.link === '#' ? undefined : n.link} style={{
            display: 'flex', gap: '14px', padding: '16px 20px',
            borderBottom: i < displayed.length - 1 ? '1px solid rgba(255,255,255,0.05)' : 'none',
            textDecoration: 'none',
            background: n.is_read ? 'transparent' : 'rgba(99,102,241,0.06)',
            transition: 'background .15s',
            cursor: n.link && n.link !== '#' ? 'pointer' : 'default',
          }}
          onMouseEnter={e => { if (n.link && n.link !== '#') e.currentTarget.style.background = 'rgba(255,255,255,0.04)'; }}
          onMouseLeave={e => { e.currentTarget.style.background = n.is_read ? 'transparent' : 'rgba(99,102,241,0.06)'; }}
          >
            {/* Icon */}
            <div style={{
              width: '42px', height: '42px', borderRadius: '12px', flexShrink: 0,
              background: n.color + '20', color: n.color,
              display: 'flex', alignItems: 'center', justifyContent: 'center',
            }}>
              <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                <path strokeLinecap="round" strokeLinejoin="round" d={iconSvg[n.icon] ?? iconSvg.bell} />
              </svg>
            </div>
            {/* Content */}
            <div style={{ flex: 1, minWidth: 0 }}>
              <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '3px' }}>
                <span style={{ fontSize: '14px', fontWeight: 700, color: '#fff' }}>{n.title}</span>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px', flexShrink: 0 }}>
                  {!n.is_read && <span style={{ width: '7px', height: '7px', borderRadius: '50%', background: '#6366f1' }} />}
                  <span style={{ fontSize: '11px', color: 'rgba(255,255,255,0.3)' }}>{n.time}</span>
                </div>
              </div>
              {n.body && (
                <p style={{ fontSize: '13px', color: 'rgba(255,255,255,0.5)', margin: 0 }}>{n.body}</p>
              )}
            </div>
          </a>
        ))}
      </div>
    </div>
  );
}
