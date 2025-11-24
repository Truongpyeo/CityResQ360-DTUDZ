import type { ReactNode } from "react";

export default function AiLayout({ children }: { children: ReactNode }) {
  return (
    <section style={{ minHeight: '100vh', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
      <div>
        {children}
      </div>
    </section>
  );
}
