import { NextResponse } from "next/server";
import type { NextRequest } from "next/server";

type Role = "citizen" | "officer" | "admin" | "guest";

function getUser(): { role: Role } {
  // Dummy user for demo purposes
  return { role: "citizen" };
}

export function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl;
  const { role } = getUser();
  const redirect = NextResponse.redirect(new URL("/", request.url));
  const citizenRoles = new Set<Role>(["citizen", "officer", "admin"]);

  if (pathname.startsWith("/ai")) {
    return NextResponse.next();
  }

  if (pathname.startsWith("/officer")) {
    if (role !== "officer") {
      return redirect;
    }
    return NextResponse.next();
  }

  if (pathname.startsWith("/admin")) {
    if (role !== "admin") {
      return redirect;
    }
    return NextResponse.next();
  }

  if (pathname.startsWith("/citizen")) {
    if (!citizenRoles.has(role)) {
      return redirect;
    }
    return NextResponse.next();
  }

  return NextResponse.next();
}

export const config = {
  matcher: ["/citizen/:path*", "/officer/:path*", "/admin/:path*", "/ai/:path*"],
};

