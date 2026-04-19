'use client';

import { useState, useEffect, useRef, useCallback } from 'react';
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

const iconSvg: Record<string, JSX.Element> = {
  cart: (
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
      <path strokeLinecap="round" strokeLinejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
    </svg>
  ),
  check: (
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
      <path strokeLinecap="round" strokeLinejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
  ),
  truck: (
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
      <path strokeLinecap="round" strokeLinejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
      <path strokeLinecap="round" strokeLinejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 2h10l2-2zM13 8h4l3 3v5h-7V8z" />
    </svg>
  ),
  gift: (
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
      <path strokeLinecap="round" strokeLinejoin="round" d="M20 12v10H4V12M22 7H2v5h20V7zM12 22V7M12 7H7.5a2.5 2.5 0 010-5C11 2 12 7 12 7zM12 7h4.5a2.5 2.5 0 000-5C13 2 12 7 12 7z" />
    </svg>
  ),
  x: (
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
      <path strokeLinecap="round" strokeLinejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
  ),
  warning: (
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
      <path strokeLinecap="round" strokeLinejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
    </svg>
  ),
  return: (
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
      <path strokeLinecap="round" strokeLinejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
    </svg>
  ),
  bell: (
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
      <path strokeLinecap="round" strokeLinejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
    </svg>
  ),
};

export default function NotificationBell() {
  const [open, setOpen]               = useState(false);
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [unread, setUnread]           = useState(0);
  const [loading, setLoading]         = useState(false);
  const [loggedIn, setLoggedIn]       = useState(false);
  const dropdownRef                   = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const token = typeof window !== 'undefined' ? localStorage.getItem('token') : null;
    setLoggedIn(!!token);
  }, []);

  const fetchNotifications = useCallback(async () => {
    const token = typeof window !== 'undefined' ? localStorage.getItem('token') : null;
    if (!token) return;
    setLoading(true);
    try {
      const res = await apiFetch('/frontend/notifications');
      if (res?.data) {
        setNotifications(res.data);
        setUnread(res.unread ?? 0);
      }
    } catch {
      // silent
    } finally {
      setLoading(false);
    }
  }, []);

  const markAllRead = useCallback(async () => {
    try {
      await apiFetch('/frontend/notifications/mark-read', { method: 'POST' });
      setUnread(0);
      setNotifications(prev => prev.map(n => ({ ...n, is_read: true })));
    } catch {
      // silent
    }
  }, []);

  // Fetch on open + mark read
  useEffect(() => {
    if (open) {
      fetchNotifications();
      if (unread > 0) markAllRead();
    }
  }, [open, fetchNotifications, markAllRead, unread]);

  // Mount + poll every 30s
  useEffect(() => {
    if (!loggedIn) return;
    fetchNotifications();
    const timer = setInterval(fetchNotifications, 30_000);
    return () => clearInterval(timer);
  }, [loggedIn, fetchNotifications]);

  // Close on outside click
  useEffect(() => {
    const handler = (e: MouseEvent) => {
      if (dropdownRef.current && !dropdownRef.current.contains(e.target as Node)) {
        setOpen(false);
      }
    };
    document.addEventListener('mousedown', handler);
    return () => document.removeEventListener('mousedown', handler);
  }, []);

  if (!loggedIn) return null;

  return (
    <div ref={dropdownRef} style={{ position: 'relative', display: 'inline-block' }}>
      {/* Bell Button */}
      <button
        onClick={() => setOpen(v => !v)}
        className="glass-icon-box"
        aria-label="Notifications"
        style={{ position: 'relative' }}
      >
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2} className="text-white">
          <path strokeLinecap="round" strokeLinejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        {unread > 0 && (
          <span style={{
            position: 'absolute', top: '-4px', insetInlineEnd: '-4px',
            minWidth: '18px', height: '18px', borderRadius: '9px',
            background: '#ef4444', color: '#fff',
            fontSize: '9px', fontWeight: 800,
            display: 'flex', alignItems: 'center', justifyContent: 'center',
            padding: '0 4px', lineHeight: 1,
            boxShadow: '0 2px 6px rgba(239,68,68,.5)',
          }}>
            {unread > 99 ? '99+' : unread}
          </span>
        )}
      </button>

      {/* Dropdown */}
      {open && (
        <div style={{
          position: 'absolute', top: 'calc(100% + 12px)', insetInlineEnd: 0,
          width: '340px', background: '#1a1a2e',
          border: '1px solid rgba(255,255,255,0.1)',
          borderRadius: '16px', boxShadow: '0 20px 60px rgba(0,0,0,0.4)',
          zIndex: 9999, overflow: 'hidden',
          textAlign: 'start',
        }}>
          {/* Header */}
          <div style={{
            display: 'flex', alignItems: 'center', justifyContent: 'space-between',
            padding: '14px 16px', borderBottom: '1px solid rgba(255,255,255,0.08)',
            textAlign: 'start',
          }}>
            <span style={{ fontSize: '13px', fontWeight: 700, color: '#fff' }}>
              Notifications
              {unread > 0 && (
                <span style={{ marginInlineStart: '8px', background: '#ef4444', color: '#fff', fontSize: '10px', fontWeight: 800, borderRadius: '10px', padding: '1px 7px' }}>
                  {unread} new
                </span>
              )}
            </span>
            <a href="/notifications" style={{ fontSize: '11px', color: '#818cf8', textDecoration: 'none', fontWeight: 600 }}>
              View all →
            </a>
          </div>

          {/* List */}
          <div style={{ maxHeight: '380px', overflowY: 'auto' }}>
            {loading && (
              <div style={{ padding: '24px', textAlign: 'center', color: 'rgba(255,255,255,0.4)', fontSize: '12px' }}>
                Loading…
              </div>
            )}
            {!loading && notifications.length === 0 && (
              <div style={{ padding: '32px', textAlign: 'center' }}>
                <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,0.2)" strokeWidth={1.5} style={{ margin: '0 auto 8px', display: 'block' }}>
                  <path strokeLinecap="round" strokeLinejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <p style={{ color: 'rgba(255,255,255,0.3)', fontSize: '13px', margin: 0 }}>No notifications yet</p>
              </div>
            )}
            {!loading && notifications.map((n) => (
              <a
                key={n.id}
                href={n.link}
                onClick={() => setOpen(false)}
                style={{
                  display: 'flex', gap: '10px', padding: '12px 16px',
                  borderBottom: '1px solid rgba(255,255,255,0.05)',
                  textDecoration: 'none', transition: 'background .15s',
                  background: n.is_read ? 'transparent' : 'rgba(99,102,241,0.06)',
                }}
                onMouseEnter={e => (e.currentTarget.style.background = 'rgba(255,255,255,0.05)')}
                onMouseLeave={e => (e.currentTarget.style.background = n.is_read ? 'transparent' : 'rgba(99,102,241,0.06)')}
              >
                {/* Icon circle */}
                <div style={{
                  width: '32px', height: '32px', borderRadius: '10px', flexShrink: 0,
                  background: n.color + '25', color: n.color,
                  display: 'flex', alignItems: 'center', justifyContent: 'center',
                }}>
                  {iconSvg[n.icon] ?? iconSvg.bell}
                </div>
                {/* Text */}
                <div style={{ flex: 1, minWidth: 0 }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                    <p style={{ fontSize: '12.5px', fontWeight: 600, color: '#fff', margin: '0 0 2px', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>
                      {n.title}
                    </p>
                    {!n.is_read && (
                      <span style={{ width: '6px', height: '6px', borderRadius: '50%', background: '#6366f1', flexShrink: 0 }} />
                    )}
                  </div>
                  <p style={{ fontSize: '11.5px', color: 'rgba(255,255,255,0.5)', margin: '0 0 3px', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>
                    {n.body}
                  </p>
                  <p style={{ fontSize: '10px', color: 'rgba(255,255,255,0.3)', margin: 0 }}>{n.time}</p>
                </div>
              </a>
            ))}
          </div>

          {/* Footer */}
          <div style={{ padding: '10px 16px', borderTop: '1px solid rgba(255,255,255,0.08)', textAlign: 'start' }}>
            <a href="/notifications" onClick={() => setOpen(false)} style={{ fontSize: '12px', color: '#818cf8', textDecoration: 'none', fontWeight: 600 }}>
              View all notifications
            </a>
          </div>
        </div>
      )}
    </div>
  );
}
