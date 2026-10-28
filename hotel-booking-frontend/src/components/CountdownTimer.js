import React, { useState, useEffect } from 'react';
import { formatCountdown } from '../utils/formatters';
import { HOLD_EXPIRY_MINUTES } from '../config';

/**
 * Countdown timer component for booking hold expiry
 * Shows remaining time before the hold expires (15 minutes default)
 */
const CountdownTimer = ({ expiresAt, onExpire }) => {
  const [remainingSeconds, setRemainingSeconds] = useState(0);

  useEffect(() => {
    const calculateRemaining = () => {
      const now = new Date();
      const expiry = new Date(expiresAt);
      const diff = Math.floor((expiry - now) / 1000);
      return Math.max(0, diff);
    };

    setRemainingSeconds(calculateRemaining());

    const interval = setInterval(() => {
      const remaining = calculateRemaining();
      setRemainingSeconds(remaining);

      if (remaining === 0) {
        clearInterval(interval);
        if (onExpire) onExpire();
      }
    }, 1000);

    return () => clearInterval(interval);
  }, [expiresAt, onExpire]);

  const minutes = Math.floor(remainingSeconds / 60);
  const isLowTime = minutes < 3;

  return (
    <div className={`countdown-timer ${isLowTime ? 'warning' : ''}`}>
      <div className="timer-icon">⏱️</div>
      <div className="timer-content">
        <div className="timer-label">Time Remaining to Complete Booking</div>
        <div className="timer-value">{formatCountdown(remainingSeconds)}</div>
        {isLowTime && (
          <div className="timer-warning">Hurry! Your reservation will expire soon</div>
        )}
      </div>
    </div>
  );
};

export default CountdownTimer;
