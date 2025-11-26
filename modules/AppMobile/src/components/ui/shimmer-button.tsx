"use client";
import React from "react";
import { motion, HTMLMotionProps } from "framer-motion";

export interface ShimmerButtonProps
  extends Omit<HTMLMotionProps<"button">, "ref"> {
  shimmerColor?: string;
  shimmerSize?: string;
  borderRadius?: string;
  shimmerDuration?: string;
  background?: string;
  children?: React.ReactNode;
}

export const ShimmerButton = React.forwardRef<
  HTMLButtonElement,
  ShimmerButtonProps
>(
  (
    {
      shimmerColor = "#ffffff",
      shimmerSize = "0.1em",
      shimmerDuration = "2s",
      borderRadius = "12px",
      background = "linear-gradient(135deg, rgb(99, 102, 241), rgb(59, 130, 246))",
      className = "",
      children,
      ...props
    },
    ref
  ) => {
    const baseClasses = "relative overflow-hidden px-8 py-4 text-base font-semibold text-white shadow-2xl transition-all before:absolute before:inset-0 before:bg-[linear-gradient(45deg,transparent_25%,rgba(255,255,255,0.3)_50%,transparent_75%,transparent_100%)] before:bg-[length:250%_250%,100%_100%] before:bg-[position:-100%_0,0_0] before:bg-no-repeat before:transition-[background-position_0s_ease] hover:before:bg-[position:200%_0,0_0] hover:before:duration-[1500ms]";
    
    return (
      <motion.button
        ref={ref}
        whileHover={{ scale: 1.05 }}
        whileTap={{ scale: 0.95 }}
        style={{
          borderRadius,
          background,
        }}
        className={`${baseClasses} ${className}`}
        {...props}
      >
        {children}
      </motion.button>
    );
  }
);

ShimmerButton.displayName = "ShimmerButton";

