"use client";
import React from "react";
import { motion } from "framer-motion";

export const BackgroundBeams = () => {
  const beams = Array.from({ length: 10 });

  return (
    <div className="absolute inset-0 overflow-hidden pointer-events-none">
      {/* Vertical beams */}
      {beams.map((_, index) => (
        <motion.div
          key={index}
          className="absolute top-0 w-[2px] h-full bg-gradient-to-b from-transparent via-brand-500/40 to-transparent blur-[1px]"
          style={{
            left: `${8 + index * 10}%`,
          }}
          initial={{ opacity: 0, y: -100 }}
          animate={{
            opacity: [0.3, 0.7, 0.3],
            y: ["-100%", "200%"],
          }}
          transition={{
            duration: 3 + index * 0.5,
            repeat: Infinity,
            ease: "linear",
            delay: index * 0.2,
          }}
        />
      ))}
      
      {/* Horizontal beams */}
      {Array.from({ length: 5 }).map((_, index) => (
        <motion.div
          key={`h-${index}`}
          className="absolute left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-brand-500/30 to-transparent blur-[1px]"
          style={{
            top: `${15 + index * 18}%`,
          }}
          initial={{ opacity: 0, x: -100 }}
          animate={{
            opacity: [0.2, 0.5, 0.2],
            x: ["-100%", "200%"],
          }}
          transition={{
            duration: 4 + index * 0.8,
            repeat: Infinity,
            ease: "linear",
            delay: index * 0.3,
          }}
        />
      ))}
    </div>
  );
};

