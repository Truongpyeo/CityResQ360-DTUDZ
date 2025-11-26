"use client";
import React, { useEffect, useState } from "react";

interface MeteorStyle {
  top: string;
  left: string;
  animationDelay: string;
  animationDuration: string;
}

export const Meteors = ({
  number = 20,
  className = "",
}: {
  number?: number;
  className?: string;
}) => {
  const [meteorStyles, setMeteorStyles] = useState<MeteorStyle[]>([]);

  useEffect(() => {
    // Generate styles only on client side after mount
    const styles = Array.from({ length: number }, () => ({
      top: Math.floor(Math.random() * 100) + "%",
      left: Math.floor(Math.random() * 100) + "%",
      animationDelay: Math.random() * 1 + 0.2 + "s",
      animationDuration: Math.floor(Math.random() * 8 + 2) + "s",
    }));
    setMeteorStyles(styles);
  }, [number]);

  if (meteorStyles.length === 0) {
    return null; // Don't render until styles are ready
  }

  return (
    <>
      {meteorStyles.map((style, idx) => (
        <span
          key={"meteor" + idx}
          className={`animate-meteor-effect absolute top-1/2 left-1/2 h-1 w-1 rounded-[9999px] bg-brand-500/60 shadow-[0_0_0_1px_#ffffff20] rotate-[215deg] ${className}`}
          style={style}
        >
          <div className="pointer-events-none absolute top-1/2 left-1/2 h-[2px] w-[80px] -translate-y-1/2 -translate-x-1/2 bg-gradient-to-r from-brand-500/0 via-brand-500/50 to-transparent" />
        </span>
      ))}
    </>
  );
};

